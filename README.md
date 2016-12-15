# LocationHistoryIngress
Script to convert Location History file from Google for IITC

# Prerequisites
- Android Phone with Google Maps History activated (https://support.google.com/accounts/answer/3118687?hl=en)
- Export your Location History with Google Takeout in KML format (https://takeout.google.com/settings/takeout/custom/location_history?hl=en&gl=US&expflags)
- Able to run PHP script to convert the file (HHVM highly recommended for performance)
- PostGIS (http://postgis.net/), GDAL (http://www.gdal.org/) and GeoServer (http://geoserver.org/) for IITC vizualisation

# Setup
- Download all the files and put them in the same folder
- Create the following folders
  - finished (will keep the final result and the number of lines of the full conversion)
  - process (original file to convert)
  - templates (to store full conversion result for update mode)
  

## Full Conversion
- Run 
```html
php convertfull.php <XML LocationHistory file name>
cp -f "finished/<XML LocationHistory file name>" "templates/<XML LocationHistory file name>"
```

Or
```html
hhvm convertfull.php <XML LocationHistory file>
cp -f "finished/<XML LocationHistory file name>" "templates/<XML LocationHistory file name>"
```

For 700 000 points, it tooks 4 hours with HHVM. (Simple benchmarking with Paris for major zone of datas)

## Update Conversion
- Run 
```html
php convertupdate.php <XML LocationHistory file>
```
Or
```html
hhvm convertupdate.php <XML LocationHistory file>
```

The update mode will take the information from the full conversion to know which datas are new and add to the template file (template is the full file after conversion). Update will fail if a full was not done before.

# Import into PostGIS database
Run
```html
ogr2ogr -f "PostgreSQL" PG:"dbname=<PostGIS Database> user=<PostGIS User> password=<PostGIS User password>" "finished/<XML LocationHistory file name>"
```
# Publish PostGIS database into GeoServer

http://docs.geoserver.org/latest/en/user/gettingstarted/postgis-quickstart/index.html

# IITC Plugin

Import 
# Limitations
- Update mode will not compare points with the Full generated file to remove too close points.
