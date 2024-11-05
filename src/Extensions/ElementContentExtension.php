<?php

namespace MoritzSauer\ContentElement;

use Bummzack\SortableFile\Forms\SortableUploadField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Dev\Debug;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use UncleCheese\Forms\ImageOptionsetField;

class ElementContentExtension extends DataExtension{

    /*DB Definition*/
    private static $db = [
        'SubTitle' => 'Text',
        'ElementStyle' => 'Text',
        'Content' => 'HTMLText',
        'SecondContent' => 'HTMLText',
        'AutoPlay' => 'Boolean(1)',
        'Loop' => 'Boolean(1)',
        'Muted' => 'Boolean(1)',
        'Controls' => 'Boolean(1)',
        'GalleryArrows' => 'Boolean(1)',
        'SubTitleBelowTitle' => 'Boolean(1)',
        'TextCenter' => 'Boolean(1)',
        'BadgePosition' => 'Text',
        'ShowImageCaption' => 'Text',
        'ImageFormat' => 'Text',
    ];

    /*HAS_ONE Definition*/
    private static $has_one = [
        'LinkedPage' => Link::class,
        'Image' => Image::class,
        'Badge' => Image::class,
        'PreviewImage' => Image::class,
        'VideoMP4' => File::class,
        'VideoOGV' => File::class,
        'VideoWEBM' => File::class,
    ];

    /*MANY_MANY Definition*/
    private static $many_many = [
        'GalleryImages' => Image::class
    ];

    /*MANY_MANY_EXTRAFIELDS Definition*/
    private static $many_many_extraFields = [
        'GalleryImages' => [
            'SortOrder' => 'Int'
        ]
    ];

    private static $owns = [
        'LinkedPage'
    ];

    private function imageFormatValues(){
        return Config::inst()->get('MoritzSauer\ContentElement')["ImageFormats"];
    }

    /*Update CMS fields*/
    public function updateCMSFields(FieldList $fields): FieldList
    {
        parent::updateCMSFields($fields);

        /*General remove*/
        $fields->removeByName([
            'HTML',
            'GalleryImages',
            'LinkedPageID',
        ]);

        /*Layout field*/
        $layoutField = ImageOptionsetField::create('ElementStyle', 'Layout wählen')->setSource($this->getLayoutOptions());
        $layoutField->setImageHeight($this->getConfigVariable('FieldSettings', 'ImageHeight'));
        $layoutField->setImageWidth($this->getConfigVariable('FieldSettings', 'ImageWidth'));
        $layoutField->setDescription($this->getConfigVariable('FieldSettings', 'FieldDescription'));

        $fields->addFieldToTab('Root.Main', $layoutField, 'Title');

        /*Get all fields*/
        $schema = DataObject::getSchema();
        $allFields = $schema->fieldSpecs($this->owner);
        $columns = array_keys($allFields);

        /*Define all fields*/
        $fields->addFieldsToTab('Root.Main', [
            TextField::create('SubTitle', 'Untertitel'),
            CheckboxField::create('SubTitleBelowTitle', 'Untertitel unter dem Titel ausgeben?')
                ->setDescription('Wenn nicht ausgewählt, wird der Untertitel über dem Titel ausgegeben.'),
            HTMLEditorField::create('Content', 'Inhalt'),
            CheckboxField::create('TextCenter', 'Zentriert?')
                ->setDescription('Soll der Inhalt zentriert ausgegeben werden?'),
            HTMLEditorField::create('SecondContent', 'Zweiter Inhalt'),
        ]);

        $fields->insertAfter('Content', LinkField::create('LinkedPage', 'Verlinkung'));

        /*Define all fields for media settings*/
        if($this->getConfigVariable('Layouts', $this->owner->ElementStyle)['hasMedia']){
            $fields->addFieldsToTab('Root.Medien', [
                UploadField::create('Image', 'Bild')
                    ->setDescription('Wird bevorzugt ausgegeben.'),
                DropdownField::create('ImageFormat', 'Seitenverhältnis', $this->imageFormatValues(), 'SixteenToNine')
                    ->setDescription('Seitenverhältnis für das Bild'),
                CheckboxField::create('ShowImageCaption', 'Bildunterschrift anzeigen?')
                    ->setDescription('Als Bildunterschrift wird der Titel des Bildes genutzt.'),
                UploadField::create('Badge', 'Badge')
                    ->setDescription('Hier kann ein Badge auf dem Bild platziert werden.'),
                DropdownField::create('BadgePosition', 'Badge - Position', [
                    'TopLeft' => 'Oben links',
                    'TopRight' => 'Oben rechts',
                    'BottomLeft' => 'Unten links',
                    'BottomRight' => 'Unten rechts',
                ])
                    ->setDescription('Hier kann die Position für den Badge gewählt werden.'),
                SortableUploadField::create('GalleryImages', 'Galerie')
                    ->setDescription('Wird alternativ zum Bild ausgegeben.'),
                CheckboxField::create('GalleryArrows', 'Pfeile bei der Galerie')
                    ->setDescription('Sollen bei der Galerie Pfeile zum Navigieren angezeigt werden?'),
                UploadField::create('PreviewImage', 'Vorschaubild')
                    ->setDescription('Vorschaubild für das Video. Dieses wird ausgegeben, bis das Video startet'),
                UploadField::create('VideoMP4', 'Video (.mp4)')
                    ->setDescription('Die Videos werden alternativ zu Bild und Galerie ausgegeben.'),
                UploadField::create('VideoOGV', 'Video (.ogv)'),
                UploadField::create('VideoWEBM', 'Video (.webm)'),
                CheckboxField::create('AutoPlay', 'Video - Autoplay?')
                    ->setDescription('Soll das Video automatisch abgespielt werden?'),
                CheckboxField::create('Loop', 'Video - Endlosschleife?')
                    ->setDescription('Soll das Video automatisch in einer Endlosschleife wiedergegeben werden?'),
                CheckboxField::create('Muted', 'Video - Stumm?')
                    ->setDescription('Soll das Video ohne Ton wiedergegeben werden?'),
                CheckboxField::create('Controls', 'Video - Steuerung?')
                    ->setDescription('Sollen die Steuerungselemente des Videos angezeigt werden?')
            ]);
        }

        /*Manually remove n:m relation because it isn't in fieldSpecs*/
        if(!$this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible']['GalleryImages']){
            $fields->removeByName('GalleryImages');
        }

        $this->owner->extend('updateContentElementCMSFields', $fields);

        /*Remove Fields depending on chosen layout and settings in .yml*/
        foreach ($columns as $field) {
            if (!in_array($field, $this->getReservedFields())) {
                if ($this->owner->ElementStyle == '') {
                    /*As long as no Layout is selected, all Fields will be removed*/
                    $fields->removeByName($field);
                    if (!$fields->dataFieldByName($field)) {
                        $field = str_replace('ID', '', $field);
                        $fields->removeByName($field);
                    }
                } else {
                    if (!$this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible'][$field]) {
                        $field = str_replace('ID', '', $field);
                        $fields->removeByName($field);
                    }
                }
            }
        }

        return $fields;
    }

    /*Get Layout options */
    private function getLayoutOptions(): array
    {
        $options = [];
        $configVars = Config::inst()->get('MoritzSauer\ContentElement')['Layouts'];
        foreach ($configVars as $layoutVar){
            $layoutID = $layoutVar['id'];
            if($this->getLayoutVariableFromConfig($layoutID)){
                if(stristr($layoutVar['imgPath'], 'themes/') !== false){
                    $img = $layoutVar['imgPath'];
                } else {
                    $img = ModuleLoader::getModule('moritz-sauer-13/contentelement')->getResource($layoutVar['imgPath']);
                    if($img){
                        $img->getURL();
                    }
                }
                $project = new \SilverStripe\Core\Manifest\Module(BASE_PATH, BASE_PATH);
                $resourcesDir = $project->getResourcesDir() ?: '_resources';
                $options[$layoutID] = [
                    'title' => $layoutVar['title'],
                    'image' => ($img) ? Director::absoluteBaseURL() . '/' . $resourcesDir . '/' . $img : '',
                ];
            }
        }
        return $options;
    }

    private function getLayoutVariableFromConfig($layout){
        return $this->getConfigVariable('Layouts', $layout)['enabled'];
    }

    private function getConfigVariable($type, $field){
        if(!$type || !$field){
            return null;
        }
        return Config::inst()->get('MoritzSauer\ContentElement')[$type][$field];
    }

    /*Define array with fields, which need to be shown the hole time*/
    private function getReservedFields(): array
    {
        return [
            'Title',
            'ShowTitle',
            'ElementStyle',
            'ExtraClass',
        ];
    }

    /*Fallback - If there is the HTML-Field in use*/
    public function HTML(){
        return $this->owner->Content;
    }

    public function ButtonCaption(){
        if($this->owner->ButtonCaption != ''){
            return $this->owner->ButtonCaption;
        }
        return _t('ContentElement.READMORE', 'Mehr erfahren');
    }

    /*render each element-style with it's own template*/
    public function renderElementStyle(){
        $template = $this->owner->ElementStyle;
        if($template != ''){
            return $this->owner->renderWith('MoritzSauer/ContentElement/ElementStyleTemplates/' . $template);
        }
        return null;
    }

    /*Check, if media is an image, a gallery or a video*/
    public function mediaTypeToDeliver(){
        if($this->owner->ImageID > 0){
            return 'Image';
        } else if($this->owner->GalleryImages() &&
            count($this->owner->GalleryImages()) > 0){
            return 'Gallery';
        } else if($this->owner->VideoMP4ID > 0 ||
            $this->owner->VideoOGVID > 0 ||
            $this->owner->VideoWEBMID > 0){
            return 'Video';
        }
        return null;
    }

    public function sortedGalleryImages(){
        return $this->owner->GalleryImages()->sort('SortOrder ASC');
    }

    public function showGalleryArrows(){
        return $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible']['GalleryArrows'] && $this->owner->GalleryArrows == 1;
    }

    public function showSubTitleBelowTitle(){
        return $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible']['SubTitleBelowTitle'] && $this->owner->SubTitleBelowTitle == 1;
    }

    public function showContentCentered(){
        return $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible']['TextCenter'] && $this->owner->TextCenter == 1;
    }

    public function showBadge(){
        return $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible']['BadgeID'] && $this->owner->BadgeID > 0;
    }

    public function showSubTitle(){
        return $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible']['SubTitle'] && $this->owner->SubTitle != '';
    }

    public function showImageCaption(){
        return $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible']['ShowImageCaption'] && $this->owner->ShowImageCaption == 1;
    }

    /*get video attributes like autoplay to set them at once*/
    public function videoAttributes(): string
    {
        $attributes = '';
        if($this->checkVideoAttribute('AutoPlay')){
            $attributes .= ' autoplay ';
        }
        if($this->checkVideoAttribute('Loop')){
            $attributes .= ' loop ';
        }
        if($this->checkVideoAttribute('Muted')){
            $attributes .= ' muted ';
        }
        if($this->checkVideoAttribute('Controls')){
            $attributes .= ' controls ';
        }
        return $attributes;
    }

    /*helper function to check each video-attribute*/
    private function checkVideoAttribute($attribute): bool
    {
        return $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible'][$attribute] && $this->owner->$attribute == 1;
    }

    public function Title()
    {
        return (str_replace("|", "&shy;", $this->owner->Title));
    }
}
