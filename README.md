Setup Instructions
Database Setup
Import the database file ebooky_db.sql from the root folder into your MySQL server.
Open app/core/Model.php and fill in your database information:
dbname – your database name
user – your database username
pass – your database password
Airtable Configuration
Open app/config.php and fill in the values for:
airtable_token
airtable_base
airtable_table
OR (recommended) set these as environment variables (see below).
Amadeus API Configuration
Open app/services/AmadeusService.php and fill in the values for:
clientId
clientSecret
Install PHP Dependencies
Open Command Prompt and navigate to the public directory:
     cd public
     composer install

Environment Variables Setup
This project uses environment variables to securely store API keys and other sensitive configuration.
Before running the application, you must set the following environment variables:
AIRTABLE_TOKEN – Your Airtable API token
AIRTABLE_BASE – Your Airtable Base ID
AIRTABLE_TABLE – The Airtable table name (e.g., registration)
How to Set Environment Variables (Windows Command Prompt):
Open Command Prompt and run:
	set AIRTABLE_TOKEN=your_airtable_token
	set AIRTABLE_BASE=your_airtable_base_id
	set AIRTABLE_TABLE=your_airtable_table_name

Note:
You need to run these commands in the same Command Prompt session where you start your PHP server, or set them permanently in your system environment variables.
If you do not set these environment variables, the application will use the default placeholder values (Your_Airtable_Token, etc.), which will not work for real API requests.


Additional Notes
Make sure the cache/ directory is writable by your web server for caching to work.
the folder cache should be at the root
