<?php

namespace MoritzSauer\ContentElement;

use Bummzack\SortableFile\Forms\SortableUploadField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\DataObject;
use UncleCheese\Forms\ImageOptionsetField;

class ElementContentExtension extends Extension
{
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

    private static $has_one = [
        'LinkedPage' => Link::class,
        'Image' => Image::class,
        'Badge' => Image::class,
        'PreviewImage' => Image::class,
        'VideoMP4' => File::class,
        'VideoOGV' => File::class,
        'VideoWEBM' => File::class,
    ];

    private static $many_many = [
        'GalleryImages' => Image::class
    ];

    private static $many_many_extraFields = [
        'GalleryImages' => [
            'SortOrder' => 'Int'
        ]
    ];

    private static $owns = [
        'LinkedPage'
    ];

    private function imageFormatValues()
    {
        return Config::inst()->get('MoritzSauer\ContentElement')["ImageFormats"];
    }

    public function updateCMSFields(FieldList $fields): FieldList
    {
        $fields->removeByName([
            'HTML',
            'GalleryImages',
            'LinkedPageID',
        ]);

        $layoutField = ImageOptionsetField::create('ElementStyle', 'Layout wählen')->setSource($this->getLayoutOptions());
        $layoutField->setImageHeight($this->getConfigVariable('FieldSettings', 'ImageHeight'));
        $layoutField->setImageWidth($this->getConfigVariable('FieldSettings', 'ImageWidth'));
        $layoutField->setDescription($this->getConfigVariable('FieldSettings', 'FieldDescription'));
        $fields->addFieldToTab('Root.Main', $layoutField, 'Title');

        $schema = DataObject::getSchema();
        $allFields = $schema->fieldSpecs($this->owner);
        $columns = array_keys($allFields);

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

        if ($this->getConfigVariable('Layouts', $this->owner->ElementStyle)['hasMedia']) {
            $fields->addFieldsToTab('Root.Medien', [
                UploadField::create('Image', 'Bild'),
                DropdownField::create('ImageFormat', 'Seitenverhältnis', $this->imageFormatValues()),
                CheckboxField::create('ShowImageCaption', 'Bildunterschrift anzeigen?'),
                UploadField::create('Badge', 'Badge'),
                DropdownField::create('BadgePosition', 'Badge - Position', [
                    'TopLeft' => 'Oben links',
                    'TopRight' => 'Oben rechts',
                    'BottomLeft' => 'Unten links',
                    'BottomRight' => 'Unten rechts',
                ]),
                SortableUploadField::create('GalleryImages', 'Galerie'),
                CheckboxField::create('GalleryArrows', 'Pfeile bei der Galerie'),
                UploadField::create('PreviewImage', 'Vorschaubild'),
                UploadField::create('VideoMP4', 'Video (.mp4)'),
                UploadField::create('VideoOGV', 'Video (.ogv)'),
                UploadField::create('VideoWEBM', 'Video (.webm)'),
                CheckboxField::create('AutoPlay', 'Video - Autoplay?'),
                CheckboxField::create('Loop', 'Video - Endlosschleife?'),
                CheckboxField::create('Muted', 'Video - Stumm?'),
                CheckboxField::create('Controls', 'Video - Steuerung?')
            ]);
        }

        if (!$this->isFieldVisible('GalleryImages')) {
            $fields->removeByName('GalleryImages');
        }

        foreach ($columns as $field) {
            if (!in_array($field, $this->getReservedFields())) {
                if ($this->owner->ElementStyle == '' || !$this->isFieldVisible($field)) {
                    $field = str_replace('ID', '', $field);
                    $fields->removeByName($field);
                }
            }
        }

        $this->owner->extend('updateContentElementCMSFields', $fields);
        return $fields;
    }

    private function isFieldVisible($field): bool
    {
        $strict = Config::inst()->get('MoritzSauer\ContentElement')['StrictFieldVisibility'] ?? false;
        $visible = $this->getConfigVariable('Layouts', $this->owner->ElementStyle)['FieldsVisible'][$field] ?? null;
        return $strict ? ($visible === true) : ($visible !== false);
    }

    private function getLayoutOptions(): array
    {
        $options = [];
        $configVars = Config::inst()->get('MoritzSauer\ContentElement')['Layouts'];
        foreach ($configVars as $layoutVar) {
            $layoutID = $layoutVar['id'];
            if ($this->getLayoutVariableFromConfig($layoutID)) {
                $img = stristr($layoutVar['imgPath'], 'themes/') !== false
                    ? $layoutVar['imgPath']
                    : ModuleLoader::getModule('moritz-sauer-13/contentelement')->getResource($layoutVar['imgPath']);
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

    private function getLayoutVariableFromConfig($layout)
    {
        return $this->getConfigVariable('Layouts', $layout)['enabled'];
    }

    private function getConfigVariable($type, $field)
    {
        if (!$type || !$field) {
            return null;
        }
        return Config::inst()->get('MoritzSauer\ContentElement')[$type][$field];
    }

    private function getReservedFields(): array
    {
        return [
            'Title',
            'ShowTitle',
            'ElementStyle',
            'ExtraClass',
        ];
    }

    public function HTML() { return $this->owner->Content; }

    public function ButtonCaption() {
        return $this->owner->ButtonCaption ?: _t('ContentElement.READMORE', 'Mehr erfahren');
    }

    public function renderElementStyle() {
        $template = $this->owner->ElementStyle;
        return $template ? $this->owner->renderWith('MoritzSauer/ContentElement/ElementStyleTemplates/' . $template) : null;
    }

    public function mediaTypeToDeliver() {
        $type = null;
        if ($this->owner->ImageID > 0) $type = 'Image';
        if (!$type && $this->owner->GalleryImages()->count() > 0) $type = 'Gallery';
        if (!$type && ($this->owner->VideoMP4ID > 0 || $this->owner->VideoOGVID > 0 || $this->owner->VideoWEBMID > 0)) $type = 'Video';
        $this->owner->invokeWithExtensions('updateMediaTypeToDeliver', $type);
        return $type;
    }

    public function sortedGalleryImages() {
        return $this->owner->GalleryImages()->sort('SortOrder ASC');
    }

    public function showGalleryArrows() {
        return $this->isFieldVisible('GalleryArrows') && $this->owner->GalleryArrows;
    }

    public function showSubTitleBelowTitle() {
        return $this->isFieldVisible('SubTitleBelowTitle') && $this->owner->SubTitleBelowTitle;
    }

    public function showContentCentered() {
        return $this->isFieldVisible('TextCenter') && $this->owner->TextCenter;
    }

    public function showBadge() {
        return $this->isFieldVisible('BadgeID') && $this->owner->BadgeID > 0;
    }

    public function showSubTitle() {
        return $this->isFieldVisible('SubTitle') && $this->owner->SubTitle;
    }

    public function showImageCaption() {
        return $this->isFieldVisible('ShowImageCaption') && $this->owner->ShowImageCaption;
    }

    public function videoAttributes(): string
    {
        $attributes = '';
        foreach (["AutoPlay", "Loop", "Muted", "Controls"] as $attr) {
            if ($this->isFieldVisible($attr) && $this->owner->$attr) {
                $attributes .= strtolower($attr) . ' ';
            }
        }
        return $attributes;
    }

    public function Title() {
        return str_replace("|", "&shy;", $this->owner->Title);
    }
}
