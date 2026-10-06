# NODEMAN: Installation  
This guide assumes a new Debian GNU/Linux 13 distribution is installed on the server, with no modifications.  
## Install dependencies  
Run the following commands in the terminal:  
```
sudo apt upgrade -y && sudo apt update -y
sudo apt install apache2 mariadb-server php8.4 php8.4-mysql php8.4-cli php8.4-xml php8.4-zip libapache2-mod-php8.4 git curl ca-certificates unzip nodejs npm -y
```
> [!NOTE]  
> If there is an error saying "Unable to locate package php8.4", run `apt search php | grep '^php[0-9]\+'` to find the version of PHP that is currently being served.  
## Clone the repository  
Run the following commands in the terminal:  
```
cd /var/www/
sudo git clone https://github.com/coolbeans1212/nodeman.git
```
## Configure Apache2  
Run the following commands in the terminal:  
```
cd /etc/apache2/sites-available
```
### If NODEMAN is the only thing you will be hosting on the server with Apache2:  
Run the following commands in the terminal:  
```
sudo nano 000-default.conf
```
Change the line that says "DocumentRoot /var/www/html" to "DocumentRoot /var/www/nodeman".  
Add the following directly below that line:  
```  
<FilesMatch "^\.env">  
    Require all denied  
</FilesMatch>  
```  
Press CTRL+X, then Y to save.  
Run the following commands:  
```
sudo systemctl reload apache
```
### If you will be hosting other things on the server with Apache2:  
Run the following commands in the terminal:  
```
sudo cp 000-default.conf nodeman.conf
sudo nano nodeman.conf
```
Change the line that says "#ServerName www.example.com" to "ServerName nodeman.YOURDOMAINNAME.invalid" (replace YOURDOMAINNAME.invalid with your actual domain name).  
Add the following directly below that line:  
```
<FilesMatch "^\.env">
    Require all denied
</FilesMatch>
```  
Press CTRL+X, then Y to save.  
Run the following commands:  
```
sudo a2ensite nodeman
sudo systemctl reload apache
```
## Set up the database  
Run the following commands:  
```
cd /var/www/nodeman/sql
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS nodeman;" && sudo mariadb nodeman < nodeman_schema.sql
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS users; && sudo mariadb users < users_schema.sql"
sudo mariadb
```
This last command will put you in the MariaDB shell. Enter the following:  
```
CREATE USER 'nodeman'@'localhost' IDENTIFIED BY 'password'; --(change password to something more secure!)  
GRANT ALL PRIVILEGES ON users.* TO 'nodeman'@'localhost';
GRANT ALL PRIVILEGES ON nodeman.* TO 'nodeman'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
## Set up .env  
Run the following commands:  
```
cd /var/www/nodeman
sudo cp .env.example .env
sudo nano .env
```
Edit the file. For example, if you followed this guide exactly and didn't change the password like you were supposed to:  
```  
DBPASSWORD=password  
DEBUG=0  
NODEBINARYLOCATION=/usr/bin/node  
LOGSLOCATION=/var/log/nodeman/  
DBADDRESS=localhost  
DBUSER=nodeman  
ACCOUNTDBNAME=users  
NODEMANDBNAME=nodeman  
```  
If you have done everything correctly so far, opening "localhost" in a web browser on the server should show a blank page, instead of a page that says something went wrong.  
## Create a user  
Right now, there is no way to create a user from the website. Do the following:  
Go to [a website that lets you make a Bcrypt hash](https://it-tools.tech/bcrypt), enter a password, and copy the hash (begins with `$2a$`).  
Run the following commands:  
```
sudo mariadb users
```
Type the following into the MariaDB shell:  
```
INSERT INTO users (username, hashed_password, admin) VALUES ('user', '$2a$10$yFhT/Va9wHfyIKIgvEHEMOMORw9EtPLsXLZKKmGIna7JOSJ2vzKCG', 1); --(replace "user" with your desired username, and "$2a$10$y..." with the hash you copied from earlier)
```
## Login to your user  
Open localhost/account/login.php and login with your details. You should be greeted with a nice background image with some icons on the side.  
## Set up permissions for www-data.  
Decide where you want NODEMAN's data to be stored. For example, if you want the projects to be stored in "/home/matei/nodeman/" and the logs in "/var/log/nodeman/":  
Run the following commands:
```
sudo mdkir /home/matei/nodeman -p
sudo chmod +x /home /home/matei /home/matei/nodeman
sudo chown www-data:www-data /home/matei/nodeman -R
sudo chmod 740 /home/matei/nodeman -R
sudo mkdir /var/log/nodeman -p
sudo chmod +x /var /var/log /var/log/nodeman
sudo chown www-data:www-data /var/log/nodeman -R
sudo chmod 740 /var/log/nodeman -R
```
If you want the logs to be stored somewhere else, you also need to edit .env with the new LOGSLOCATION.  
## Create a project!  
The UI looks a bit broken when there are no projects, so go ahead and press "Add or view projects", and then "Add"! Make sure "Filepath" is contained within the directory where you want the projects to be stored, and end it with a "/" character. Main file will almost always be "index.js". If anything breaks, toggle DEBUG=1 in .env, open the network tab in DevTools, and make a GitHub issue describing the problem and paste the response from the request that doesn't work properly.