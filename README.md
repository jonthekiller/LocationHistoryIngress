# LocationHistoryIngress
Script to convert Location History file from Google for IITC

# Prerequisites
- Android Phone with Google Maps History activated (https://support.google.com/accounts/answer/3118687?hl=en)
- Export your Location History with Google Takeout in KML format (https://takeout.google.com/settings/takeout/custom/location_history?hl=en&gl=US&expflags)
- Able to run PHP script to convert the file (HHVM highly recommended for performance)
- PostGIS and GeoServer for IITC vizualisation

# Setup
- Download all the files and put them in the same folder
- Create the following folders
  - finished (will keep the final result and the number of lines of the full conversion)
  - process (original file to convert)
  - templates (to store full conversion result for update mode)
  

## Full Conversion
- Run <pre>php convertfull.php \<XML LocationHistory file name>
cp -f "finished/\<XML LocationHistory file name>" "templates/\<XML LocationHistory file name>"</pre>

Or
<pre>hhvm convertfull.php \<XML LocationHistory file></pre>

For 700 000 points, it can took 4 hours with HHVM.

## Update Conversion
- Run <pre>php convertupdate.php \<XML LocationHistory file></pre>
Or
<pre>hhvm convertupdate.php \<XML LocationHistory file></pre>
The update mode will take the information from the full conversion to know which datas are new and add to the template file (template is the full file after conversion)
