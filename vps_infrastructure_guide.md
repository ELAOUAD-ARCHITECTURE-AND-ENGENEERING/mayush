# VPS Infrastructure Setup Guide

To ensure the **Abandoned Cart Emails** and **Search Optimizations** work correctly on your VPS, you need to set up two system-level services.

---

## 1. Automated Task Scheduler (Cron)
Laravel's scheduler must run every minute to check if any abandoned cart emails need to be sent.

1. SSH into your VPS as the `mayushdesign` user (or as root).
2. Open the crontab:
   ```bash
   crontab -e
   ```
3. Add this line to the bottom of the file (ensure the path matches your `APP_DIR`):
   ```bash
   * * * * * /usr/bin/php8.2 /home/mayushdesign/public_html/artisan schedule:run >> /dev/null 2>&1
   ```
4. Save and exit.

---

## 2. Background Queue Worker (Supervisor)
To process emails in the background without making the user wait, you need a process monitor like **Supervisor** to keep the queue worker running.

1. Install Supervisor (if not already installed):
   ```bash
   sudo apt-get install supervisor
   ```
2. Create a new configuration file:
   ```bash
   sudo nano /etc/supervisor/conf.d/mayush-worker.conf
   ```
3. Paste the following configuration:
   ```ini
   [program:mayush-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=/usr/bin/php8.2 /home/mayushdesign/public_html/artisan queue:work --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   user=mayushdesign
   redirect_stderr=true
   stdout_logfile=/home/mayushdesign/public_html/storage/logs/worker.log
   stopwaitsecs=3600
   ```
4. Save and exit, then run these commands to start it:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start mayush-worker:*
   ```

---

## 3. Summary of Files to Transfer
When moving to the server, ensure these modified/new files are included:
- **`.env`**: (Update your local one or use the deployment script to sync it).
- **`app/Console/Kernel.php`**: Contains the hourly schedule.
- **`app/Console/Commands/SendAbandonedCartReminders.php`**: The new logic.
- **`app/Utility/EmailUtility.php`**: The email sending method.
- **`app/Http/Controllers/SearchController.php`**: Search optimizations.
- **`database/migrations/`**: All new migration files.
- **`resources/views/emails/abandoned_cart.blade.php`**: The premium email template.
- **`webpack.mix.js`**: Asset configuration.
