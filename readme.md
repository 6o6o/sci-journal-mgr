# Scientific journal manager
A simple and straightforward software for maintaining a website of a scientific journal or any other periodical literature which shares the same structure as most scholarly articles or research papers, including title, author, abstract, references, CrossRef DOI URLs, etc.

Written in PHP, front-end mostly uses various Bootstrap components.

For demonstration check out http://ukrbotj.co.ua, or http://algologia.co.ua

# Setup
1. Create table structure by importing __empty.sql__ to your database.
2. Put the files into __Document Root__ directory on your server.
3. Edit the following files:
 * __index.php__
    * `J_NAME` - Full journal name
    * `J_ABBR` - Official abbreviation
    * `J_LANG` - Default publication language (most used)
    * `J_YEAR` - Year founded
    * `Meta description` used on front page
 * __inc/dbconn.php__ - your database `host`, `name`, `user` and `password`
 * __pages/*.html__
    * Each filename corresponds to a page in top navigation menus respectively
    * tools.html - optional page with additional features.
 * __img/logo.gif__ - journal logo (100 x 100)
 * __favicon.ico__
 
To add new pages, alter or remove existing you'll need to modify the following arrays in __index.php__ in form of `path => fullname` and ensure the included files exist in _pages_ directory with either `php` or `html` extension
  * `$page` - main navigation menu
  * `$assist` - upper right complementary menu

# Usage
Adding new content and managing existing is possible for authorized users with appropriate permissions. Create a new user, activate it, authorize and set admin privileges (the first user created needs to do this manually, in the db, users table, set `priv = 4`).

Navigate to _newabs_ page and you'll see a form with a bunch of hollow inputs. I made an effort to automate this as much as possible, so if you click on "Autofill" link above the form, you'll get another input, which, upon paste will parse the text, filter illegal characters, add HTML formatting (italics, sub- and superscripts) and fill in the fields below. More on this later...