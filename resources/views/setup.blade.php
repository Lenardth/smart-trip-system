<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .btn { padding: 10px 20px; margin: 10px; cursor: pointer; border: none; border-radius: 5px; }
        .btn-primary { background: #4CAF50; color: white; }
        .output { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Database Setup</h1>
    <p>Click the button below to run migrations:</p>
    
    <button class="btn btn-primary" onclick="runMigrations()">Run Migrations</button>
    
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
            })
            .catch(err => {
                output.textContent = 'Error: ' + err.message;
            });
        }
    </script>
</body>
</html>
