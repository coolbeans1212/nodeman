# NODEMAN
PHP Utility for managing node.js projects on GNU/Linux systems.
## Requirements
- PHP 8.x.xx (tested on PHP 8.4.24)
- Node.js
## How to use
- Copy .env.example to .env and replace the placeholder values with the values you want to use.
- Make sure that www-data has access to all the files and paths listed in .env and the paths that you will be storing the node.js projects in, and make sure that www-data can traverse the directories (for example, if you store your node.js projects in /home/pi/, run `chmod +x /home /home/pi`).
- write more later LOL