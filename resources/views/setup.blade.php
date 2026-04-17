<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .btn { padding: 10px 20px; margin: 10px; cursor: pointer; border: none; border-radius: 5px; font-size: 14px; }
        .btn-primary { background: #4CAF50; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .output { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; white-space: pre-wrap; font-family: monospace; font-size: 12px; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Database Setup</h1>
    
    <div class="warning">
        <strong>⚠️ Warning:</strong> If migrations are failing, use "Fresh Migrate" to drop all tables and recreate them.
    </div>
    
    <button class="btn btn-primary" onclick="runMigrations()">Run Migrations</button>
    <button class="btn btn-danger" onclick="freshMigrate()">Fresh Migrate (Drop All Tables)</button>
    <button class="btn btn-primary" onclick="checkStatus()">Check Database Status</button>
    
    <div id="output" class="output" style="display:none;"></div>

    <script>
        function runMigrations() {
            const output = document.getElementById('output');
            output.style.display = 'block';
            output.textContent = 'Running migrations...';
            
            fetch('/setup/migrate', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                output.textContent = JSON.stringify(data, null, 2);
                if (data.success) {
                    output.style.background = '#d4edda';
                } else {
                    output.style.background = '#f8d7da';
                }
            })
            .catch(err => {
                output.textContent = 'Error: ' + err.message;
                output.style.background = '#f8d7da';
            });
        }

        function freshMigrate() {
            if (!confirm('This will DROP ALL TABLES and recreate them. All data will be lost. Continue?')) {
                return;
            }
            
            const output = document.getElementById('output');
            output.style.display = 'block';
            output.textContent = 'Running fresh migration (dropping all tables)...';
            
            fetch('/setup/fresh', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                output.textContent = JSON.stringify(data, null, 2);
                if (data.success) {
                    output.style.background = '#d4edda';
                } else {
                    output.style.background = '#f8d7da';
                }
            })
            .catch(err => {
                output.textContent = 'Error: ' + err.message;
                output.style.background = '#f8d7da';
            });
        }

        function checkStatus() {
            const output = document.getElementById('output');
            output.style.display = 'block';
            output.textContent = 'Checking database status...';
            
            fetch('/setup/status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                output.textContent = JSON.stringify(data, null, 2);
                if (data.success) {
                    output.style.background = '#d4edda';
                } else {
                    output.style.background = '#f8d7da';
                }
            })
            .catch(err => {
                output.textContent = 'Error: ' + err.message;
                output.style.background = '#f8d7da';
            });
        }
    </script>
</body>
</html>
