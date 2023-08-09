# MAP.js
### Visit this Trello link to add requests: [trello](https://trello.com/invite/b/syoLKyJ6/ATTIdbd3124d66f353f18258e426f4c3c7c3711EEEEE/mapjs)
## Setup:


### Install & Activate the plugin 
Download the latest release zip from GitHub. The plugin can be found here: [rc-map](https://github.com/robertocannella/rc-map/releases/). Upload the zip to WordPress and activate the plugin.

### Configuration

Click Map.js in the dashboard side panel to reveal the Map.js Options

First, Click on `Main Options` tab. Here enter the Principal POI location information. 

Next, in the `Google MAP` tab, add your Google Maps API key.
Additionally, you can enter the zoom level, default center point of the map, and any loaded map styles. 
One preloaded style comes with the plugin. Additional styles can be configured [here](https://snazzymaps.com/) and loading into the `Load Style` Tab.

Then, load the data into the `Load Data` tab. Paste in tab separated text. Ideally, just copy from an xls file.  Do not include headings or any fields not listed in the tab. If you'd like to replace all the existing Points of Interest, tick the Replace all checkbox.  
Click import data.  If your API key is valid, this will import the data, retrieve any missing geo_codes from the Google API and update each post. If you run the import without a valid API key, you can subsequently run the `Get Geocodes` script from the `Generate` tab after you've updated the API key.







