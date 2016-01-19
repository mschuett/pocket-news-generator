=== Pocket News Generator ===
Contributors: marubon
Donate link: 
Tags: pocket, post, news
Requires at least: 3.2
Tested up to: 3.8
Stable tag: 0.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pocket News Generator retrieves your Pocket data based on specified condition and generates its HTML code according to specified format.

== Description ==

Pocket News Generator retrieves your Pocket data based on specified condition and generates its HTML code according to specified format.
This plugin makes it possible to make article creation which introduces bookmarked articles more efficient by effective use of Pocket data, 
its data retrieval and code generation function. 


This plugin has the following features:

= Flexible Pocket data retrieval based on search condition = 

  Target article can be retrieved according to specified condition set out in search form.

= Flexible HTML format  =

  Format of generated HTML code is modifiable because the format is configurable at setting page.

== Installation ==

= Download and Install =

1. Download zip archive file from this repository.

2. Login as an administrator to your WordPress admin page. 
   Using the "Add New" menu option under the "Plugins" section of the navigation, 
   Click the "Upload" link, find the .zip file you download and then click "Install Now". 
   You can also unzip and upload the plugin to your plugins directory (i.e. wp-content/plugins/) through FTP/SFTP. 

3. Activate the "Pocket News Generator" plugin through the "Plugins" menu in your WordPress admin page.

= Setting =

1. Open the setting page for "Pocket News Generator".

2. Follow the guide in the section titled "Get Access Token".

3. At Step 1, click the "Pocket My Application" link. 
   In the "My Applications" page of Pocket site, click the "CREATE AN APPLICATION" button.
   In the "Create an Application" page, input required information in order to 
   register "Pocket News Generator" and get a "consumer key" for "Pocket News Generator".
   Input "Pocket News Generator" to "Application Name" field.
   Input "WordPress plugin" to "Application Description" field.
   Check the "Retrieve items from a user's list" checkbox in the "Permission" field.
   Check the "Web" checkbox in the "Platforms" field.
   Check the "I accept the Terms of Service" checkbox and click the "CREATE APPLICATION" button.

4. Copy the published consumer key of "Pocket News Generator" in the "My Applications" page of Pocket site.

5. Proceed to Step 2. 
   Input the obtained consumer key to the form labled "Consumer Key".
   Click the "Get Request Code" button.

6. Proceed to Step 3.
   Click the "Authorize Request Toekn" button. 
   In the authentication page in pocket site, click the "Authorize" button.

7. Proceed to Step 4. 
   Click the "Get Access Token" button.

8. Proceed to Step 5. 
   Copy the given consumer key and access token and paste them into the correspondent form
   in the section titled "Register New Parameter".
   Click the "Register" button.

9. Copy the HTML format in the "Format Sample" area and paste it to the form labeled "HTML Format".
   Click the "Register" button again.  

= Pocket data retrieval and its HTML code generation =

1. Open the tool page for "Pocket News Generator".

2. Specify search condition in order to retrieve Pocket data.
   Click "Generate" button.

3. HTML code based on retrieved Pocket data and registered HTML format is generated in the area labeled HTML Code.
   Copy the HTML code and paste it into your editing page. 

== Frequently Asked Questions ==
There are no questions.

== Screenshots ==
1. Prameter registration form in setting page
2. Guide to get a consumer key and access token  
3. Search form for pocket data retrieval
4. Sample of generated HTML code

== Changelog ==

= 0.2.0 =
* Added language translation for Japanese
* Improved accuracy of information retrieval for site name and its URL
* Modified page design of plugin  

= 0.1.2 =
* Changed reference priority between content of og:description and excerpt contained in Pocket 

= 0.1.1 =
* Reserved words are added in order to refer to web site name and its URL giving bookmarked item.
* Existing reserved words was modified. Old reserved words need to be replaced with new reserved words.
* Bug fix: search condition "Since" does not work correctly.

= 0.1.0 =
* Initial working version.

== Upgrade Notice ==
There is no upgrade notice.

== Arbitrary section ==

