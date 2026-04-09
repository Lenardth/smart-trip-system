/**
 * iPhone-style Photo Editor
 * Canvas-based: filters, brightness/contrast/saturation/warmth/vignette, crop
 */

var PE = (function () {
    var canvas, ctx, origImage, currentFilter = 'none';
    var cropRatio = 'free';
    var cropBox   = { x: 0, y: 0, w: 0, h: 0 };
    var isDragging = false, isResizing = false, dragHandle = null;
    var dragStart  = { x: 0, y: 0 };
    var mediaIndex = -1;

    var FILTERS = {
        none:       { brightness: 0, contrast: 0, saturation: 0, warmth: 0 },
        vivid:      { brightness: 10, contrast: 20, saturation: 40, warmth: 5 },
        dramatic:   { brightness: -10, contrast: 40, saturation: 20, warmth: -5 },
        mono:       { brightness: 5, contrast: 15, saturation: -100, warmth: 0 },
        silvertone: { brightness: 10, contrast: 10, saturation: -80, warmth: 10 },
        noir:       { brightness: -20, contrast: 50, saturation: -100, warmth: -10 },
        fade:       { brightness: 20, contrast: -20, saturation: -20, warmth: 5 },
        warm:       { brightness: 5, contrast: 5, saturation: 10, warmth: 40 },
        cool:       { brightness: 5, contrast: 5, saturation: 5, warmth: -40 },
    };

    function open(index, mediaLibrary) {
        mediaIndex = index;
        var item = mediaLibrary[index];
        if (!item || item.type !== 'image') return;

        canvas = document.getElementById('peCanvas');
        if (!canvas) return;
        ctx = canvas.getContext('2d');

        var nameEl = document.getElementById('peFileName');
        if (nameEl) nameEl.textContent = item.name || item.file_name || '';

        resetSliders();
        currentFilter = 'none';
        document.querySelectorAll('.pe-filter-item').forEach(function(el) { el.classList.remove('active'); });
        var firstFilter = document.querySelector('.pe-filter-item[onclick*="none"]');
        if (firstFilter) firstFilter.classList.add('active');

        var img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function () {
            origImage = img;
            var wrap = canvas.parentElement;
            var maxW = (wrap ? wrap.clientWidth  : 600) || 600;
            var maxH = (wrap ? wrap.clientHeight : 400) || 400;
            var scale = Math.min(maxW / img.width, maxH / img.height, 1);
            canvas.width  = Math.round(img.width  * scale);
            canvas.height = Math.round(img.height * scale);
            applyAll();
            renderThumbs(img);
            initCropBox();
        };
        img.src = item.src;
    }

    function close() {
        // Just reset state — the canvas is part of the viewer, not a separate overlay
        mediaIndex = -1;
        origImage  = null;
    }

    function resetSliders() {
        ['peBrightness','peContrast','peSaturation','peWarmth','peVignette'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.value = 0;
        });
        ['peBrightnessVal','peContrastVal','peSaturationVal','peWarmthVal','peVignetteVal'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.textContent = '0';
        });
    }

    function getSlider(id) {
        var el = document.getElementById(id);
        return el ? parseInt(el.value, 10) : 0;
    }

    function applyAll() {
        if (!origImage || !canvas || !ctx) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(origImage, 0, 0, canvas.width, canvas.height);

        var brightness  = getSlider('peBrightness');
        var contrast    = getSlider('peContrast');
        var saturation  = getSlider('peSaturation');
        var warmth      = getSlider('peWarmth');
        var vignette    = getSlider('peVignette');

        // Add filter preset on top of manual sliders
        var preset = FILTERS[currentFilter] || FILTERS.none;
        brightness  += preset.brightness;
        contrast    += preset.contrast;
        saturation  += preset.saturation;
        warmth      += preset.warmth;

        var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        var data = imageData.data;

        var contrastFactor = (259 * (contrast + 255)) / (255 * (259 - contrast));

        for (var i = 0; i < data.length; i += 4) {
            var r = data[i], g = data[i+1], b = data[i+2];

            // Brightness
            r += brightness * 2.55;
            g += brightness * 2.55;
            b += brightness * 2.55;

            // Contrast
            r = contrastFactor * (r - 128) + 128;
            g = contrastFactor * (g - 128) + 128;
            b = contrastFactor * (b - 128) + 128;

            // Warmth (shift red/blue channels)
            r += warmth * 0.8;
            b -= warmth * 0.8;

            // Saturation via luminance
            var lum = 0.299 * r + 0.587 * g + 0.114 * b;
            var sf  = (saturation + 100) / 100;
            r = lum + sf * (r - lum);
            g = lum + sf * (g - lum);
            b = lum + sf * (b - lum);

            data[i]   = Math.max(0, Math.min(255, r));
            data[i+1] = Math.max(0, Math.min(255, g));
            data[i+2] = Math.max(0, Math.min(255, b));
        }

        ctx.putImageData(imageData, 0, 0);

        // Vignette
        if (vignette > 0) {
            var cx = canvas.width / 2, cy = canvas.height / 2;
            var radius = Math.sqrt(cx * cx + cy * cy);
            var grad = ctx.createRadialGradient(cx, cy, radius * 0.4, cx, cy, radius);
            grad.addColorStop(0, 'rgba(0,0,0,0)');
            grad.addColorStop(1, 'rgba(0,0,0,' + (vignette / 100 * 0.85) + ')');
            ctx.fillStyle = grad;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        // Update value labels
        document.getElementById('peBrightnessVal') && (document.getElementById('peBrightnessVal').textContent = getSlider('peBrightness'));
        document.getElementById('peContrastVal')   && (document.getElementById('peContrastVal').textContent   = getSlider('peContrast'));
        document.getElementById('peSaturationVal') && (document.getElementById('peSaturationVal').textContent = getSlider('peSaturation'));
        document.getElementById('peWarmthVal')     && (document.getElementById('peWarmthVal').textContent     = getSlider('peWarmth'));
        document.getElementById('peVignetteVal')   && (document.getElementById('peVignetteVal').textContent   = getSlider('peVignette'));
    }

    function setFilter(name) {
        currentFilter = name;
        applyAll();
    }

    function renderThumbs(img) {
        document.querySelectorAll('.pe-filter-thumb').forEach(function(tc) {
            var filter = tc.dataset.filter;
            tc.width  = 72;
            tc.height = 72;
            var tctx = tc.getContext('2d');
            tctx.drawImage(img, 0, 0, 72, 72);

            var preset = FILTERS[filter] || FILTERS.none;
            if (filter === 'none') return;

            var id = tctx.getImageData(0, 0, 72, 72);
            var d  = id.data;
            var cf = (259 * (preset.contrast + 255)) / (255 * (259 - preset.contrast));

            for (var i = 0; i < d.length; i += 4) {
                var r = d[i], g = d[i+1], b = d[i+2];
                r += preset.brightness * 2.55;
                g += preset.brightness * 2.55;
                b += preset.brightness * 2.55;
                r = cf * (r - 128) + 128;
                g = cf * (g - 128) + 128;
                b = cf * (b - 128) + 128;
                r += preset.warmth * 0.8; b -= preset.warmth * 0.8;
                var lum = 0.299*r + 0.587*g + 0.114*b;
                var sf  = (preset.saturation + 100) / 100;
                r = lum + sf*(r-lum); g = lum + sf*(g-lum); b = lum + sf*(b-lum);
                d[i]   = Math.max(0, Math.min(255, r));
                d[i+1] = Math.max(0, Math.min(255, g));
                d[i+2] = Math.max(0, Math.min(255, b));
            }
            tctx.putImageData(id, 0, 0);
        });
    }

    function initCropBox() {
        var pad = 20;
        cropBox = { x: pad, y: pad, w: canvas.width - pad*2, h: canvas.height - pad*2 };
    }

    function setCropRatio(ratio) {
        cropRatio = ratio;
        if (ratio === 'free') return;
        var parts = ratio.split(':');
        var rw = parseInt(parts[0]), rh = parseInt(parts[1]);
        var maxW = canvas.width  - 40;
        var maxH = canvas.height - 40;
        var w = maxW, h = Math.round(w * rh / rw);
        if (h > maxH) { h = maxH; w = Math.round(h * rw / rh); }
        cropBox = { x: Math.round((canvas.width - w) / 2), y: Math.round((canvas.height - h) / 2), w: w, h: h };
    }

    function applyCrop() {
        if (!origImage || !canvas || !ctx) return;
        var scaleX = origImage.width  / canvas.width;
        var scaleY = origImage.height / canvas.height;
        var sx = cropBox.x * scaleX, sy = cropBox.y * scaleY;
        var sw = cropBox.w * scaleX, sh = cropBox.h * scaleY;

        var tmp = document.createElement('canvas');
        tmp.width  = cropBox.w;
        tmp.height = cropBox.h;
        var tctx = tmp.getContext('2d');
        tctx.drawImage(canvas, cropBox.x, cropBox.y, cropBox.w, cropBox.h, 0, 0, cropBox.w, cropBox.h);

        canvas.width  = cropBox.w;
        canvas.height = cropBox.h;
        ctx.drawImage(tmp, 0, 0);

        // Update origImage to cropped version
        var newImg = new Image();
        newImg.onload = function() { origImage = newImg; initCropBox(); };
        newImg.src = canvas.toDataURL();
    }

    function resetCrop() { initCropBox(); }

    function reset() {
        resetSliders();
        currentFilter = 'none';
        document.querySelectorAll('.pe-filter-item').forEach(function(el) { el.classList.remove('active'); });
        var first = document.querySelector('.pe-filter-item[onclick*="none"]');
        if (first) first.classList.add('active');
        applyAll();
    }

    function switchTab(tab) {
        ['adjust','filters','crop'].forEach(function(t) {
            var panel = document.getElementById('pe-panel-' + t);
            if (panel) panel.style.display = t === tab ? '' : 'none';
        });
        var cropOverlay = document.getElementById('peCropOverlay');
        if (cropOverlay) cropOverlay.style.display = tab === 'crop' ? 'block' : 'none';
    }

    function getEditedDataUrl() {
        return canvas ? canvas.toDataURL('image/jpeg', 0.92) : null;
    }

    return {
        open:         open,
        close:        close,
        applyAll:     applyAll,
        setFilter:    setFilter,
        setCropRatio: setCropRatio,
        applyCrop:    applyCrop,
        resetCrop:    resetCrop,
        reset:        reset,
        switchTab:    switchTab,
        getDataUrl:   getEditedDataUrl,
        getIndex:     function() { return mediaIndex; },
    };
})();

// ── Wire to window ────────────────────────────────────────────────────────────
window.peApply      = function() { PE.applyAll(); };
window.peSetFilter  = function(name, el) {
    document.querySelectorAll('.pe-filter-item').forEach(function(e) { e.classList.remove('active'); });
    if (el) el.classList.add('active');
    PE.setFilter(name);
};
window.peCropRatio  = function(r, el) {
    document.querySelectorAll('.pe-ratio-btn').forEach(function(e) { e.classList.remove('active'); });
    if (el) el.classList.add('active');
    PE.setCropRatio(r);
};
window.peApplyCrop  = function() { PE.applyCrop(); };
window.peResetCrop  = function() { PE.resetCrop(); };
window.peReset      = function() { PE.reset(); };
window.peSwitchTab  = function(tab, el) {
    document.querySelectorAll('.pe-tab').forEach(function(e) { e.classList.remove('active'); });
    if (el) el.classList.add('active');
    PE.switchTab(tab);
};
window.closePhotoEditor = function() { PE.close(); };

window.savePhotoEdit = function() {
    var dataUrl = PE.getDataUrl();
    var index   = PE.getIndex();
    if (!dataUrl || index < 0) { PE.close(); return; }

    // Convert dataUrl to blob and upload as new version
    fetch(dataUrl)
        .then(function(r) { return r.blob(); })
        .then(function(blob) {
            var fd = new FormData();
            fd.append('media[]', blob, 'edited-photo.jpg');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            return fetch('/api/media/upload', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
                body: fd,
            });
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            PE.close();
            if (typeof loadMediaFromServer === 'function') loadMediaFromServer();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Saved!', text: 'Edited photo uploaded.', icon: 'success', timer: 1500, showConfirmButton: false });
            }
        })
        .catch(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Error', text: 'Could not save edited photo.', icon: 'error' });
            }
        });
};

window.openPhotoEditor = function(index, mediaLibrary) {
    PE.open(index, mediaLibrary);
};
