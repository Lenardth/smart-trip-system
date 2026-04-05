(function() {
    const TIMEOUT_DURATION = 30 * 60 * 1000; 
    const WARNING_TIME = 5 * 60 * 1000; 
    const CHECK_INTERVAL = 60 * 1000; 

    let warningShown = false;
    let lastActivity = Date.now();
    let timeoutTimer = null;
    let checkTimer = null;

    
    function updateActivity() {
        lastActivity = Date.now();
        warningShown = false;
        hideWarning();
        resetTimers();
    }

    
    function checkTimeout() {
        const elapsed = Date.now() - lastActivity;
        const remaining = TIMEOUT_DURATION - elapsed;

        if (remaining <= 0) {
            
            logout('Your session has expired due to inactivity. Please log in again.');
        } else if (remaining <= WARNING_TIME && !warningShown) {
            
            showWarning(Math.ceil(remaining / 60000));
            warningShown = true;
        }

        
        fetch('/check-activity', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            if (!response.ok || response.status === 401) {
                logout('Your session has ended. Please log in again.');
            }
        }).catch(() => {
            
        });
    }

    
    function showWarning(minutes) {
        const banner = document.createElement('div');
        banner.id = 'timeout-warning';
        banner.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            text-align: center;
            z-index: 10000;
            border-bottom: 3px solid #ffc107;
            font-family: 'Georgia', serif;
            font-size: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        `;
        banner.innerHTML = `
            <strong><i class="fas fa-exclamation-triangle"></i> Session Expiring Soon!</strong><br>
            Your session will expire in approximately ${minutes} minute(s) due to inactivity.
            <button onclick="window.sessionRefresh()" style="
                background: #856404;
                color: white;
                border: none;
                padding: 8px 20px;
                border-radius: 4px;
                cursor: pointer;
                margin-left: 15px;
                font-weight: bold;
                font-family: 'Georgia', serif;
            ">Stay Logged In</button>
        `;
        document.body.appendChild(banner);
    }

    
    function hideWarning() {
        const banner = document.getElementById('timeout-warning');
        if (banner) {
            banner.remove();
        }
    }

    
    function logout(message) {
        if (message) {
            sessionStorage.setItem('logout_message', message);
        }
        window.location.href = '/logout-expired';
    }

    
    function resetTimers() {
        if (timeoutTimer) clearTimeout(timeoutTimer);
        if (checkTimer) clearInterval(checkTimer);

        timeoutTimer = setTimeout(() => {
            logout('Your session has expired due to inactivity.');
        }, TIMEOUT_DURATION);

        checkTimer = setInterval(checkTimeout, CHECK_INTERVAL);
    }

    
    const events = ['mousedown', 'keypress', 'scroll', 'touchstart', 'click'];
    events.forEach(event => {
        document.addEventListener(event, updateActivity, true);
    });

    
    window.sessionRefresh = function() {
        updateActivity();
        
        fetch('/check-activity', {
            method: 'GET',
            credentials: 'same-origin'
        });
    };

    
    updateActivity();

    
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkTimeout();
        }
    });
})();