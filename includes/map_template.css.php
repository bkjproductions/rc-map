


#map h2 {
    margin-top: 0;
    font-size: 18px;
}

#map {
    height: 100vh;
    position: relative;
    overflow: hidden;
    width: 100%;
    min-height: 500px;
    font-family: "Open Sans", Arial, Sans-serif;
    flex: 1;
    max-height: 80vh;
    display: block;
}

#map-categories {
    font-family: "Open Sans", Arial, Sans-serif;
}

#map-categories ul, #map-categories li {
    margin: 0;
    padding: 0;
    display: inline-block;
    margin-right: 1em;
}

#map-categories {
    padding: 1em;
    text-align: center;
    background-color: #eee;
}

body[data-elementor-device-mode="mobile"] #map-categories {text-align: left;}
body[data-elementor-device-mode="mobile"] #map-categories li {width: 40%;}

#map-categories a {
    text-decoration: none;
    color: grey;
    text-transform: uppercase;
    font-size: 12px;
    line-height: 1em;
    letter-spacing: .05em;
}

#map-categories a::before {
    content: ' ';
    width: .75em;
    height: .75em;
    margin-right: .5em;
    margin-left: .5em;
    display: block;
    border-radius: 1000px;
    float: left;
    margin-top: .75em;
}

#map-categories a:hover {
    opacity: .7;
}

#map-categories a.selected {
    color: black;
}

a#all-link {
    padding-left: 0;
}

#All-link:before {
    background-color: black;
}

/* customize these IDs and colors: */
#All-link:before {background-color: black;}
{{CUSTOM_CSS}}





#map {background: url('/images/butterfly1.gif') no-repeat center center;}
#map {background-size:  150px 150px ;}

.map-loaded #map {background: none;}
