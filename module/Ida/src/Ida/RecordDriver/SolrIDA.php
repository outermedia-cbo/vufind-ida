<?php
/**
 * Abstract super class for Record Drivers for IDA Items.
 * User: boehm
 * Date: 6/4/14
 * Time: 3:21 PM
 */
namespace Ida\RecordDriver;

use VuFind\RecordDriver\SolrDefault;

abstract class SolrIDA extends SolrDefault
{

    function __construct($mainConfig = null, $recordConfig = null,
                         $searchSettings = null)
    {
        $this->formats = $mainConfig->Format2Thumbs->formats;
        parent::__construct($mainConfig, $recordConfig, $searchSettings);
    }

     /**
     * Deduplicate author information into associative array with main/corporate/
     * secondary keys.
     *
     * @return array [main] and [additional] authors
     */
    public function getDeduplicatedAuthors()
    {
        $authors = array(
            'main' => $this->getPrimaryAuthor(),
            'additional' => $this->getAdditionalAuthors(),
        );

        // The additional author array may contain a primary author;
        // let's be sure we filter out duplicate values.
        $duplicates = array();
        if (!empty($authors['main'])) {
            $duplicates[] = $authors['main'];
        }
        if (!empty($authors['additional'])) {
            $authors['additional'] = array_diff($authors['additional'], $duplicates);
        }

        return $authors;
    }

    public function getAdditionalAuthors()
    {
        return $this->getMulitValuedField("author_additional");
    }

    /**
    * return array [[topic], [geo], [person]]
    */
    public function getAllSubjectHeadings()
    {

        $topic = $this->getTopics();
        $geo = $this->getGeographicTopics();
        $person = $this->getPersonTopics();

        $retval = array();
        if (!empty($topic))
        {
            $retval["topic"] = $topic;
        }
        if (!empty($geo))
        {
            $retval["geo"] = $geo;
        }
        if (!empty($person))
        {
            $retval["person"] = $person;
        }

        return $retval;
    }

    public function getPlacesOfPublication()
    {
        return $this->getMulitValuedField("placeOfPublication");
    }

    public function getSeriesNr()
    {
        return $this->getSingleValuedField('seriesNr');
    }

    public function getTranslatedTerms()
    {
        return $this->getMulitValuedField("translatedTerms");
    }

    public function getDisplayTitle()
    {
        return !empty($this->getTitleSub()) ?
            $this->getShortTitle() . " : " . $this->getTitleSub() : $this->getTitle();
    }

    public function getTitleSub()
    {
        return $this->getSingleValuedField('title_sub');
    }

    public function getEditors()
    {
        return $this->getMulitValuedField("editor");
    }

    public function getEntities()
    {
        return $this->getMulitValuedField("entity");
    }

    public function getPublishers()
    {
        return $this->getMulitValuedField("publisher");
    }

    /**
    * Single valued
    */
    public function getDisplayPublicationDate()
    {
        return $this->getSingleValuedField('displayPublishDate');
    }

    public function getDimensions()
    {
        return $this->getMulitValuedField("dimension");
    }

    public function getRunTimes()
    {
        return $this->getMulitValuedField("runtTime");
    }

    /**
    * @return array
    */
    public function getTopics()
    {
        return $this->getMulitValuedField("topic");
    }

    /**
     * @return array
    */
    public function getGeographicTopics()
    {
        return $this->getMulitValuedField("subjectGeographic");
    }

    /**
     * @return array
    */
    public function getPersonTopics()
    {
        return $this->getMulitValuedField("subjectPerson");
    }

    /**
    * @return array
    */
    public function getZDBIDs()
    {
        return $this->getMulitValuedField("zdbId");
    }

    public function getShelfMark()
    {
        return $this->getSingleValuedField('shelfMark');
    }

    public function getDescription()
    {
        return $this->getSingleValuedField('description');
    }

    public function getProjects()
    {
        return $this->getMulitValuedField("project");
    }

    public function getTypeOfRessource()
    {
        return $this->getMulitValuedField("typeOfRessource");
    }

    public function getLanguageCodes()
    {
        return $this->getMulitValuedField("language_code");
    }

    protected function getSingleValuedField($fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : '';
    }

    protected function getMulitValuedField($fieldName)
    {
        return isset($this->fields[$fieldName]) && is_array($this->fields[$fieldName]) ? $this->fields[$fieldName] : array();
    }

    /**
     * Expects one entry for systematik_parent_id and systematik_parent_title
     * @return array [id, tittle]
     */
    public function getBelongsTo()
    {
        if (isset($this->fields['systematik_parent_id']) && isset($this->fields['systematik_parent_title']))
        {
            return array($this->fields['systematik_parent_id'][0], $this->fields['systematik_parent_title'][0]);
        }
        return  array();
    }

    /**
     * Expects one entry for hierarchy_top_id and hierarchy_top_title
     * @return array [id, tittle]
     */
    public function getBelongsToTop()
    {
        if(in_array('Artikel', $this->getFormats()))
        {
            if (isset($this->fields['hierarchy_top_id']) && isset($this->fields['hierarchy_top_title']))
            {
                return array($this->fields['hierarchy_top_id'][0], $this->fields['hierarchy_top_title'][0]);
            }
        }
        return array();
    }

    /**
     * Returns one of three things: a full URL to a thumbnail preview of the record
     * if an image is available in an external system; an array of parameters to
     * send to VuFind's internal cover generator if no fixed URL exists; or false
     * if no thumbnail can be generated.
     *
     * @param string $size Size of thumbnail (small, medium or large -- small is
     * default).
     *
     * @return string|array|bool
     */
    public function getThumbnail($size = 'small')
    {
        $thumbnail = array('size' => $size, 'contenttype' => $this->getFormatForThumb());
        if ($isbn = $this->getCleanISBN()) {
            $thumbnail['isn'] = $isbn;
        }
        return $thumbnail;
    }

    /**
     * @return string
     */
    private function getFormatForThumb()
    {
        $formats = $this->getFormats();
        $format = null;

        if (!empty($formats))
        {
            $format = $this->formats->get($formats[0]);
        }
        return $format;
    }

    /**
     * Return an XML representation of the record using the specified format.
     * Return false if the format is unsupported.
     *
     * @param string     $format     Name of format to use (corresponds with OAI-PMH
     * metadataPrefix parameter).
     * @param string     $baseUrl    Base URL of host containing VuFind (optional;
     * may be used to inject record URLs into XML when appropriate).
     * @param RecordLink $recordLink Record link helper (optional; may be used to
     * inject record URLs into XML when appropriate).
     *
     * @return mixed         XML, or false if format unsupported.
     */
    public function getXML($format, $baseUrl = null, $recordLink = null)
    {
        if ($format != 'oai_dc')
        {
            // Unsupported format
           return false;
        }

        // For OAI-PMH Dublin Core, produce the necessary XML:
        $dc = 'http://purl.org/dc/elements/1.1/';
        $xsi='http://www.w3.org/2001/XMLSchema-instance';
        $xml = new \SimpleXMLElement(
            '<oai_dc:dc '
            . 'xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" '
            . 'xmlns:dc="' . $dc . '" '
            . 'xmlns:xsi="'.$xsi.'" '
            . 'xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ '
            . 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd" />'
        );

        $xml->addChild('title', htmlspecialchars($this->getDisplayTitle()), $dc);
        // Authors
        $primary = $this->getPrimaryAuthor();
        if (!empty($primary))
        {
            $xml->addChild('creator', htmlspecialchars($primary), $dc);
        }
        foreach ($this->getAdditionalAuthors() as $current)
        {
            $xml->addChild('creator', htmlspecialchars($current), $dc);
        }
        // Editors
        foreach ($this->getEditors() as $current)
        {
            $xml->addChild('creator', htmlspecialchars($current), $dc);
        }

        // Entity (Körperschaft)
        foreach ($this->getEntities() as $current)
        {
            $xml->addChild('creator', htmlspecialchars($current), $dc);
        }

        // Language
        foreach ($this->getLanguages() as $lang)
        {
            $xml->addChild('language', htmlspecialchars($lang), $dc);
        }

        foreach ($this->getLanguageCodes() as $lang)
        {
            if ($lang != "none")
            {
                $child = $xml->addChild('language', htmlspecialchars($lang), $dc);
                $child->addAttribute('xsi:type', 'dcterms:ISO639-3', $xsi);
            }
        }

        // Publisher
        foreach ($this->getPublishers() as $pub)
        {
            $xml->addChild('publisher', htmlspecialchars($pub), $dc);
        }

        // Date
        $date = $this->getDisplayPublicationDate();
        if (!empty($date))
        {
            $xml->addChild('date', htmlspecialchars($date), $dc);
        }

        // format: physical
        foreach ($this->getPhysicalDescriptions() as $current)
        {
            $xml->addChild('format', htmlspecialchars($current), $dc);
        }

        // format: dimension
        foreach ($this->getDimensions() as $current)
        {
            $xml->addChild('format', htmlspecialchars($current), $dc);
        }

        // format: runTime
        foreach ($this->getRunTimes() as $current)
        {
            $xml->addChild('format', htmlspecialchars($current), $dc);
        }

        // subjects
        foreach ($this->getTopics() as $subj)
        {
            $xml->addChild('subject', htmlspecialchars($subj), $dc);
        }
        foreach ($this->getPersonTopics() as $subj)
        {
            $xml->addChild('subject', htmlspecialchars($subj), $dc);
        }
        foreach ($this->getGeographicTopics() as $subj)
        {
            $xml->addChild('coverage', htmlspecialchars($subj), $dc);
        }

        // identifier
        foreach ($this->getISBNs() as $identifier)
        {
            $xml->addChild('identifier', htmlspecialchars($identifier), $dc);
        }
        foreach ($this->getISSNs() as $identifier)
        {
            $xml->addChild('identifier', htmlspecialchars($identifier), $dc);
        }
        foreach ($this->getZDBIDs() as $identifier)
        {
            $xml->addChild('identifier', htmlspecialchars($identifier), $dc);
        }
        $xml->addChild('identifier', htmlspecialchars($this->getUniqueID()), $dc);
        if (null !== $baseUrl && null !== $recordLink)
        {
            $url = $baseUrl . $recordLink->getUrl($this);
            $xml->addChild('identifier', $url, $dc);
        }
        $shelfMark = $this->getShelfMark();
        if (!empty($shelfMark))
        {
            $xml->addChild('identifier', htmlspecialchars($shelfMark), $dc);
        }

        // description
        $projects = $this->getProjects();
        foreach ($projects as $current)
        {
            $xml->addChild('description', htmlspecialchars($current), $dc);
        }
        $desc = $this->getDescription();
        if (!empty($desc))
        {
            $xml->addChild('description', htmlspecialchars($desc), $dc);
        }

        // type of ressource
        $typeOfRessource = $this->getTypeOfRessource();
        foreach ($typeOfRessource as $current)
        {
            $xml->addChild('type', htmlspecialchars($current), $dc);
        }

        $formats=$this->getFormats();
        if (!empty($formats) && $formats[0] == "Artikel" && null !== $baseUrl && null !== $recordLink)
        {
            foreach ($this->getHierarchyTopID() as $current)
            {

                $url = $baseUrl . $recordLink->getUrl($current);
                $xml->addChild('source', $url, $dc);
            }
        }
        return $xml->asXml();
    }

}