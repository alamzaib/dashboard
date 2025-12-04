# MySQL Setup Instructions

## Quick Setup (Windows/XAMPP)

1. **Make sure XAMPP MySQL is running**
   - Open XAMPP Control Panel
   - Start MySQL service

2. **Run the setup script:**
   ```bash
   cd backend
   setup-database.bat
   ```

   Or manually run these commands:
   ```bash
   # Update .env file
   php setup-mysql.php
   
   # Create database (if MySQL is in PATH)
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Or use XAMPP MySQL path directly
   C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Run migrations
   php artisan migrate
   
   # Seed test users
   php artisan db:seed --class=TestUsersSeeder
   ```

## Manual Setup

### Step 1: Create Database

Using phpMyAdmin:
1. Open http://localhost/phpmyadmin
2. Click "New" to create a new database
3. Name it: `dashboard`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

Or using MySQL command line:
```sql
CREATE DATABASE IF NOT EXISTS `dashboard` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 2: Update .env File

Edit `backend/.env` and update these lines:
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=dashboard
DB_USERNAME=root
DB_PASSWORD=
```

Or run:
```bash
php setup-mysql.php
```

### Step 3: Run Migrations

```bash
cd backend
php artisan migrate
```

### Step 4: Create Test Users

```bash
php artisan db:seed --class=TestUsersSeeder
```

This will create 20 test users:
- Email: testuser1@example.com to testuser20@example.com
- Password: test1234

## Test Users Created

After running the seeder, you can login with any of these accounts:

| Email | Password |
|-------|----------|
| testuser1@example.com | test1234 |
| testuser2@example.com | test1234 |
| ... | ... |
| testuser20@example.com | test1234 |

## Verify Setup

Check if users were created:
```bash
php artisan tinker
```

Then run:
```php
\App\Models\User::count(); // Should return 20
\App\Models\User::pluck('email');
```

