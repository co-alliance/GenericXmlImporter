<?php
/**
 * Standalone version to generate a Csv file via XmlImport and via a xsl sheet.
 *
 * TODO This class is not used by the plugin and was not checked for Omeka 2.0.
 */
class XmlImport_generateCsv extends Process
{
    public function run($args)
    {
        // Get variables from args array passed into detached process.
        $filepath = $args['filepath'];
        $filename = !empty($args['csv_filename'])
            ? $args['csv_filename']
            : pathinfo($filename, PATHINFO_BASENAME);

        $format = $args['format'];
        $itemTypeId = $args['item_type_id'];
        $collectionId = $args['collection_id'];
        $createCollections = $args['create_collections'];
        $recordsArePublic = $args['public'];
        $recordsAreFeatured = $args['featured'];
        $elementsAreHtml = $args['html_elements'];
        $containsExtraData = $args['extra_data'];
        $tagName = $args['tag_name'];
        $columnDelimiter = $args['column_delimiter'];
        $enclosure = $args['enclosure'];
        $elementDelimiter = $args['element_delimiter'];
        $tagDelimiter = $args['tag_delimiter'];
        $fileDelimiter = $args['file_delimiter'];
        $stylesheetParameters = $args['stylesheet_parameters'];

        $stylesheet = !empty($args['stylesheet'])
            ? $args['stylesheet']
            : get_option('xml_import_stylesheet');
        $csvfilesdir = !empty($args['destination_dir'])
            ? $args['destination_dir']
            : sys_get_temp_dir();

        // Create a DOM document and load the XML data.
        $xml_doc = new DomDocument;
        $xml_doc->load($filepath);

         // Create a DOM document and load the XSL stylesheet.
        $xsl = new DomDocument;
        $xsl->load($stylesheet);

        // Import the XSL styelsheet into the XSLT process.
        $xp = new XsltProcessor();
        $xp->setParameter('', 'node', $tagName);
        $xp->importStylesheet($xsl);

        // Write transformed xml file to the temp csv file.
        try {
            if ($doc = $xp->transformToXML($xml_doc)) {
                $csvFilename = $csvfilesdir . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME) . '.csv';
                $documentFile = fopen($csvFilename, 'w');
                fwrite($documentFile, $doc);
                fclose($documentFile);

                //$this->_initializeCsvImport($basename, $recordsArePublic, $recordsAreFeatured, $collectionId);
                $this->_helper->flashMessenger(__('Successfully generated CSV File'));
            } else {
                $this->_helper->flashMessenger(__('Could not transform XML file.  Be sure your XML document is valid.'), 'error');
            }
        } catch (Exception $e){
            $this->view->error = $e->getMessage();
        }
    }
}
