#!/bin/bash

# Smart Booking Authentication - Quick Setup Script
# Run this from your Laravel project root directory

echo "╔════════════════════════════════════════════════════════╗"
echo "║  Smart Booking - Authentication Setup                 ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Check if we're in a Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: Not in a Laravel project directory!"
    echo "Please run this script from your Laravel project root."
    exit 1
fi

echo "✓ Laravel project detected"
echo ""

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p app/Http/Controllers
mkdir -p resources/views/auth
mkdir -p public/css
mkdir -p database/migrations
echo "✓ Directories created"
echo ""

# Copy Controller
echo "📋 Installing AuthController..."
if [ -f "AuthController.php" ]; then
    cp AuthController.php app/Http/Controllers/
    echo "✓ AuthController installed"
else
    echo "⚠️  Warning: AuthController.php not found"
fi

# Copy User Model
echo "📋 Installing User model..."
if [ -f "User.php" ]; then
    cp User.php app/Models/
    echo "✓ User model installed"
else
    echo "⚠️  Warning: User.php not found"
fi

# Copy Views
echo "📋 Installing view files..."
for view in login.blade.php register.blade.php dashboard.blade.php plan-trip.blade.php discover.blade.php destinations.blade.php community.blade.php index.blade.php; do
    if [ -f "$view" ]; then
        cp "$view" resources/views/
        echo "  ✓ $view"
    else
        echo "  ⚠️  $view not found"
    fi
done

# Copy Routes
echo "📋 Installing routes..."
if [ -f "web.php" ]; then
    cp web.php routes/
    echo "✓ Routes installed"
else
    echo "⚠️  Warning: web.php not found"
fi

# Copy Migration
echo "📋 Installing migration..."
if [ -f "2024_01_01_000000_create_users_table.php" ]; then
    cp 2024_01_01_000000_create_users_table.php database/migrations/
    echo "✓ Migration installed"
else
    echo "⚠️  Warning: Migration file not found"
fi

# Copy CSS
echo "📋 Installing CSS files..."
if [ -f "auth.css" ]; then
    cp auth.css public/css/
    echo "✓ Auth CSS installed"
else
    echo "⚠️  Warning: auth.css not found"
fi

echo ""
echo "══════════════════════════════════════════════════════════"
echo "📦 Files installed successfully!"
echo "══════════════════════════════════════════════════════════"
echo ""
echo "Next steps:"
echo ""
echo "1. Configure your .env file:"
echo "   DB_DATABASE=smart_trip_system"
echo "   DB_USERNAME=root"
echo "   DB_PASSWORD=your_password"
echo "   SESSION_DRIVER=database"
echo ""
echo "2. Create the database:"
echo "   mysql -u root -p"
echo "   CREATE DATABASE smart_trip_system;"
echo "   exit;"
echo ""
echo "3. Run migrations:"
echo "   php artisan migrate"
echo ""
echo "4. Clear cache:"
echo "   php artisan config:clear"
echo "   php artisan cache:clear"
echo "   composer dump-autoload"
echo ""
echo "5. Start the server:"
echo "   php artisan serve"
echo ""
echo "6. Visit http://localhost:8000/register to create your first user!"
echo ""
echo "══════════════════════════════════════════════════════════"
echo "✨ Setup complete! Read INSTALLATION_GUIDE.md for details."
echo "══════════════════════════════════════════════════════════"
