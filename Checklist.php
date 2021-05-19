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
    copy_to_html(array(
                    '<style>',
                    '.cols {',
                    '   display: flex;',
                    '   width: 100%;',
                    '}',
                    '</style>',
                )
    );
    $categories = [];
    foreach ($XML->category as $cat) {
        array_push($categories, array("category"=>$cat, "lines"=>sizeof($cat->item)));
    }
    function cmp($a, $b) {
        return $b['lines'] - $a['lines'];
    }
    usort($categories, "cmp");
    $columns = 0;
    foreach ($categories as $c) {
        $cat = $c['category'];
        if ($columns == 0) {
            echo "<div class=\"cols\">\n";
        }
        $category = $cat->attributes();
        echo "<div class=\"category_list\">\n";
        echo "<p style=\"font-size: x-large\">";
        echo     $category['name'], "\n";
        echo "</p>\n";
        foreach ($cat->item as $item) {
            $items = $item->attributes();
            $id = $category['name']. "/" . $items['name'];
            echo "<input class=\"inventoryitem\" type=\"checkbox\" id=\"$id\" onchange=\"checkboxchanged(this)\">";
            echo "<label for=\"$id\">", $items['name'], "</label><br>\n";
        }
        echo "</div>\n";
        if (++$columns == 5) {
            echo "</div>\n";
            $columns = 0;
        }
    }
    if ($columns != 0) {
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

    '<br><button type="button" onclick="clearallcheckboxes()">Uncheck All</button>',

    '<style id="compiled-css" type="text/css">',

    '/*   ------------------------------------------------------------- */',
    '.category_list {',
    '    padding: 5px;',
    '    margin: 5px;',
    '    width: 20%;',
    '    max-width: 250px;',
    '    border: 2px solid black;',
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

    'function storagename(id) {',
    '    return "' . $_REQUEST["name"] . '/" + id;',
    '}',

    'function checkboxchanged(cb) {',
    '    if (cb.checked) {',
    '        localStorage.setItem(storagename(cb.id), "1");',
    '    } else {',
    '        localStorage.removeItem(storagename(cb.id));',
    '    }',
    '}',

    'function clearallcheckboxes() {',
    '    var cb = document.getElementsByClassName("inventoryitem");',
    '    for (var i = 0; i < cb.length; i++) {',
    '        localStorage.removeItem(storagename(cb[i].id));',
    '        cb[i].checked = 0;',
    '    }',
    '}',

    #initialize state of all checkboxes based on local storage
    'var cb = document.getElementsByClassName("inventoryitem");',
    'for (var i = 0; i < cb.length; i++) {',
    '    if (localStorage.getItem(storagename(cb[i].id))) {',
    '       cb[i].checked = 1;',
    '    }',
    '}',

    '</script>',

    '</body>',
    '</html>',

    )
);

#------------------------------------------------------------------------------
?>

