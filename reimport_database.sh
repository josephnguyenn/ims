#!/bin/bash
# Re-import Database with Proper UTF-8mb4 Encoding
# Run on production server

echo "=========================================="
echo "IMS Database Re-Import Script"
echo "=========================================="
echo ""

# Variables
DB_NAME="doan"
DB_USER="doan"
DB_HOST="localhost"

# Check if SQL file is provided
if [ -z "$1" ]; then
    echo "Usage: ./reimport_database.sh <path-to-sql-file>"
    echo "Example: ./reimport_database.sh ~/ims_backup_20251111.sql"
    exit 1
fi

SQL_FILE="$1"

# Check if file exists
if [ ! -f "$SQL_FILE" ]; then
    echo "Error: SQL file not found: $SQL_FILE"
    exit 1
fi

echo "Database: $DB_NAME"
echo "SQL File: $SQL_FILE"
echo ""

# Confirmation
read -p "This will DROP and RE-CREATE the database. Continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Aborted."
    exit 0
fi

echo ""
echo "Step 1: Backing up current database..."
BACKUP_FILE="backup_before_reimport_$(date +%Y%m%d_%H%M%S).sql"
mysqldump -u $DB_USER -p --default-character-set=utf8mb4 $DB_NAME > ~/$BACKUP_FILE
echo "✓ Backup saved: ~/$BACKUP_FILE"
echo ""

echo "Step 2: Dropping database..."
mysql -u $DB_USER -p -e "DROP DATABASE IF EXISTS $DB_NAME;"
echo "✓ Database dropped"
echo ""

echo "Step 3: Creating database with utf8mb4..."
mysql -u $DB_USER -p -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "✓ Database created with utf8mb4"
echo ""

echo "Step 4: Importing data..."
mysql -u $DB_USER -p --default-character-set=utf8mb4 $DB_NAME < $SQL_FILE
echo "✓ Data imported"
echo ""

echo "Step 5: Verifying tables..."
mysql -u $DB_USER -p -e "USE $DB_NAME; SHOW TABLES;"
echo ""

echo "=========================================="
echo "✅ Database re-imported successfully!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Clear Laravel cache: php artisan config:cache"
echo "2. Test the application"
echo "3. Check if Czech characters display correctly"
echo ""
