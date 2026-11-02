# NODEMAN: Installation with Docker
This guide assumes you already have Docker installed on an amd64 or arm64 processor.
> [!NOTE]
> You can install this manually. See INSTALL.md
Make a new directory. Inside that directory, make a file named `.env` containing DB_PASSWORD=CHANGEME, but actually change CHANGEME to something more secure.  
Run the following commands inside that directory (if you are on Windows®, run this in cmd instead of PowerShell):
```
curl -O https://raw.githubusercontent.com/coolbeans1212/nodeman/main/docker-compose.yml
docker compose up -d
```
If you are on Windows®, run the following commands in PowerShell:
```
$hash = docker compose exec -T web php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
"INSERT INTO users (username, password, admin) VALUES ('user', '$hash', 1);" | docker compose exec -T db sh -c 'mariadb -u nodeman -p"$MARIADB_PASSWORD" nodeman'
```
(replace `yourpassword` with a secure password and `user` with your desired username)
If you are on any other operating system, run the following commands:
```
HASH=$(docker compose exec -T web php -r 'echo password_hash("yourpassword", PASSWORD_BCRYPT);')
printf "INSERT INTO users (username, password, admin) VALUES ('user', '%s', 1);\n" "$HASH" | docker compose exec -T db sh -c 'mariadb -u nodeman -p"$MARIADB_PASSWORD" nodeman'
```
(replace `yourpassword` with a secure password and `user` with your desired username)
## Access NODEMAN and create a project!  
Open http://localhost/ in a browser window to access NODEMAN, and enter the login credentials you supplied earlier.
The UI looks a bit broken when there are no projects, so go ahead and press "Add or view projects", and then "Add"! Make sure "Filepath" begins with "/home/nodeman/projects/". Main file will almost always be "index.js".