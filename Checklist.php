<?php

#------------------------------------------------------------------------------
define('DATAFILE_DIR'  , '/home/pi/Share/Data/jrw/Checklists/');

#------------------------------------------------------------------------------
#copy the html to the output
function copy_to_html($html_array)
{
    foreach ($html_array as $line) {
        if (gettype($line) === 'array') {
            # an array, handle it recursively
            copy_to_html($line);
        } else if (gettype($line) === 'string') {
            if (is_callable($line)) {
                # line is function reference, call it
                $line();
            } else {
               # copy the line to the output
                echo "$line\n";
            }
        } else {
            echo '<p>Unknown data type in copy_to_html - ', gettype($line), '</p>';
        }
    }
}

#------------------------------------------------------------------------------
$XML = simplexml_load_file(DATAFILE_DIR . $_REQUEST['name']);
if ($XML === false) {
    echo "Failed loading XML\n";
}

#------------------------------------------------------------------------------
function output_inventory() {
    global $XML;

    $categories = [];
    foreach ($XML->category as $cat) {
        array_push($categories, array("category"=>$cat, "lines"=>sizeof($cat->item)));
    }
    usort($categories, function ($a, $b) {
                           return $b['lines'] - $a['lines'];
                       }
         );

    foreach ($categories as $c) {
        $cat = $c['category'];
        $category = $cat->attributes();
        echo "<div class=\"category_list\">\n";
        echo "<strong>";
        echo     $category['name'], "\n";
        echo "</strong><br>\n";
        foreach ($cat->item as $item) {
            $items = $item->attributes();
            $id = $category['name']. "/" . $items['name'];
            echo "<input class=\"inventoryitem\" type=\"checkbox\" id=\"$id\" onchange=\"CheckboxChanged(this)\">";
            echo "<label for=\"$id\">", $items['name'], "</label><br>\n";
        }
        echo "</div>\n";
    }
}

#------------------------------------------------------------------------------
copy_to_html(array(
    '<!DOCTYPE html',
    '	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"',
    '	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
    '<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">',
    '<head>',
    '<title>', 
    $_REQUEST['name'],
    '</title>',
    '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />',
    '</head>',

    '<body>',

    'output_inventory',

    # stop floating ...
    '<div style="clear:both;"></div>',

    '<br><button type="button" onclick="ClearAllCheckboxes()">Uncheck All</button>',

    '<style id="compiled-css" type="text/css">',

    '/*   ------------------------------------------------------------- */',
    '.category_list {',
    '    padding: 5px;',
    '    margin: 5px;',
    '    width: 250px;',
    '    height: 300px;',
    '    border: 2px solid black;',
    '    float: left;',
    '    overflow: auto;',
    '}',

    '/*   ------------------------------------------------------------- */',
    '.itemlabel {',
    '    color: black;',
    '    font-size: large;',
    '    list-style-type: none;',
    '}',

    '/*   ------------------------------------------------------------- */',
    '</style>',

    '<script>',

    'function StorageName(id) {',
    '    return "' . $_REQUEST["name"] . '/" + id;',
    '}',

    'function SetBackgroundIfAllChecked(cl) {',
    '    catcb = cl.getElementsByClassName("inventoryitem");',
    '    for (var i = 0; i < catcb.length; i++) {',
    '         if (catcb[i].checked == 0) {',
    '             cl.style.background = "white";',
    '             return;',
    '         }',
    '    }',
    '    cl.style.background = "lightgray";',
    '}',

    'function CheckboxChanged(cb) {',
    '    if (cb.checked) {',
    '        localStorage.setItem(StorageName(cb.id), "1");',
    '    } else {',
    '        localStorage.removeItem(StorageName(cb.id));',
    '    }',
    '    SetBackgroundIfAllChecked(cb.parentElement);',
    '}',

    'function ClearAllCheckboxes() {',
    '    if (confirm("Clear all check boxes?")) {',
    '        var cb = document.getElementsByClassName("inventoryitem");',
    '        for (var i = 0; i < cb.length; i++) {',
    '            localStorage.removeItem(StorageName(cb[i].id));',
    '            cb[i].checked = 0;',
    '        }',
    '        for (var i = 0; i < cl.length; i++) {',
    '            cl[i].style.background = "white";',
    '        }',
    '    }',
    '}',

    #initialize state of all checkboxes based on local storage
    'var cb = document.getElementsByClassName("inventoryitem");',
    'for (var i = 0; i < cb.length; i++) {',
    '    if (localStorage.getItem(StorageName(cb[i].id))) {',
    '       cb[i].checked = 1;',
    '    }',
    '}',

    #set the background for all categories with all items checked
    'var cl = document.getElementsByClassName("category_list");',
    'for (var i = 0; i < cl.length; i++) {',
    '    SetBackgroundIfAllChecked(cl[i]);',
    '}',

    '</script>',

    '</body>',
    '</html>',

    )
);

#------------------------------------------------------------------------------
?>

