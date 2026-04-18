// Destination Info Page - News Loading

(function () {
    const destData = window.__destinationData || {};
    const DEST = destData.name || '';
    const COUNTRY = destData.country || '';

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function loadDestinationNews() {
        const newsContent = document.getElementById('newsContent');
        if (!newsContent || !DEST) return;

        fetch('/api/destination-news?destination=' + encodeURIComponent(DEST) + '&country=' + encodeURIComponent(COUNTRY), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { 
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json(); 
        })
        .then(function (res) {
            const articles = res.articles || [];
            
            if (!articles.length) {
                newsContent.innerHTML = 
                    '<div style="text-align:center;padding:50px;color:var(--text-muted);background:#fafafa;border-radius:8px;">' +
                        '<i class="fas fa-newspaper" style="font-size:48px;opacity:0.3;color:var(--gold);"></i>' +
                        '<p style="margin-top:20px;font-size:16px;">No recent news available for this destination.</p>' +
                        '<p style="font-size:14px;margin-top:8px;">Check back later for updates.</p>' +
                    '</div>';
                return;
            }

            newsContent.innerHTML = '<div style="display:grid;gap:20px;">' +
                articles.map(function (article) {
                    const date = article.publishedAt 
                        ? new Date(article.publishedAt).toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                          }) 
                        : '';
                    const source = article.source?.name || 'News Source';
                    const img = article.image || 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=600&q=80';
                    const title = article.title || 'Untitled';
                    const description = article.description || '';
                    
                    return '<a href="' + esc(article.url) + '" target="_blank" rel="noopener noreferrer" class="news-article" ' +
                        'style="display:flex;gap:20px;padding:24px;border:1px solid var(--border);border-radius:8px;text-decoration:none;' +
                        'transition:all 0.3s;background:#fff;align-items:flex-start;">' +
                        '<div style="width:180px;height:120px;flex-shrink:0;border-radius:6px;' +
                        'background:linear-gradient(rgba(0,0,0,0.1), rgba(0,0,0,0.1)), url(\'' + esc(img) + '\') center/cover;' +
                        'box-shadow:0 2px 8px rgba(0,0,0,0.1);"></div>' +
                        '<div style="flex:1;min-width:0;">' +
                            '<h3 style="margin:0 0 10px;font-size:18px;color:var(--deep);line-height:1.4;font-weight:600;">' + 
                                esc(title) + 
                            '</h3>' +
                            '<p style="margin:0 0 12px;font-size:14px;color:var(--text);line-height:1.6;' +
                            'display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">' + 
                                esc(description) + 
                            '</p>' +
                            '<div style="display:flex;gap:16px;font-size:13px;color:var(--text-muted);flex-wrap:wrap;">' +
                                '<span style="display:flex;align-items:center;gap:6px;">' +
                                    '<i class="fas fa-newspaper"></i> ' + esc(source) +
                                '</span>' +
                                (date ? '<span style="display:flex;align-items:center;gap:6px;">' +
                                    '<i class="fas fa-calendar"></i> ' + date +
                                '</span>' : '') +
                            '</div>' +
                        '</div>' +
                        '<div style="display:flex;align-items:center;color:var(--gold);font-size:18px;">' +
                            '<i class="fas fa-external-link-alt"></i>' +
                        '</div>' +
                    '</a>';
                }).join('') +
            '</div>';

            // Add hover effect
            const style = document.createElement('style');
            style.textContent = 
                '.news-article:hover { ' +
                    'transform: translateY(-3px); ' +
                    'box-shadow: 0 8px 24px rgba(59,31,43,0.15); ' +
                    'border-color: var(--gold); ' +
                '}';
            if (!document.getElementById('news-hover-style')) {
                style.id = 'news-hover-style';
                document.head.appendChild(style);
            }
        })
        .catch(function (err) {
            console.error('News loading error:', err);
            newsContent.innerHTML = 
                '<div style="text-align:center;padding:50px;color:var(--text-muted);background:#fff5f5;border-radius:8px;border:1px solid #ffcccc;">' +
                    '<i class="fas fa-exclamation-circle" style="font-size:36px;color:#e74c3c;"></i>' +
                    '<p style="margin-top:16px;font-size:16px;color:var(--text);">Could not load news at this time.</p>' +
                    '<p style="font-size:14px;margin-top:8px;">Please try again later.</p>' +
                '</div>';
        });
    }

    // Load news when page is ready
    if (document.readyState !== 'loading') {
        loadDestinationNews();
    } else {
        document.addEventListener('DOMContentLoaded', loadDestinationNews);
    }
})();
