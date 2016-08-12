<?php
namespace ride\update;
use ride\application\system\System;
class ModuleUpdate {
    public static function checkVersion() {
        include_once __DIR__ . '/../../../../../autoload.php';
        $parameters = __DIR__ . '/../../../../../application/config/parameters.php';
        if (file_exists($parameters)) {
            include_once $parameters;
        }
        if (!is_array($parameters)) {
            $parameters = null;
        }
        $composerFile = file_get_contents(__DIR__ .'/../../../composer.json');
        $jsonComposerFile = json_decode($composerFile);
        // If the version we are installing is lower then 0.22, run this update
        $update059= version_compare($jsonComposerFile->version, '0.5.9', '<');
        if($update059) {
            ModuleUpdate::doUpdate059($parameters);
        }
    }
    private static function doUpdate059($parameters) {
        $system = new System($parameters);
        $fileBrowser = $system->getFileBrowser();
        $fileSystem = $system->getFileSystem();
        $dependencyInjector = $system->getDependencyInjector();
        $appDirectory = $fileBrowser->getApplicationDirectory();

        $ormManager = $dependencyInjector->get("ride\\library\\orm\\OrmManager");
        $textModel = $ormManager->getModel('Text');
        $assetModel = $ormManager->getModel('Asset');
        $folder = $ormManager->getAssetFolderModel()->getById(1);
        $texts = $textModel->find();
        $data = array();
        foreach($texts as $text) {
            if(!empty($text->getImage())) {
                $data[$text->getId()] = $text->getImage();
            }
        }
        $assets = array();
        foreach($data as $id => $image) {

            $source = $appDirectory->getChild($image);

            var_dump($source);
            $destination = $appDirectory->getChild('data/upload/assets/' . $source->getName());
            $destination = $destination->getCopyFile();
            if($source->isReadable()) {
                $source->copy($destination);
                $asset = $assetModel->createEntry();
                $asset->setName($source->getName());
                $asset->setFolder($folder);
                $asset->setValue(str_replace($appDirectory->getAbsolutePath() . '/', '', $destination->getAbsolutePath()));
                $assetModel->save($asset);
                $assets[$id] = $asset->getId();
            }
        }
        $system->execute('php application/cli.php od');
        $system->execute('php application/cli.php og');

        foreach($assets as $id => $assetId) {
            $asset = $assetModel->getById($assetId);
            $t = $textModel->getById($id);
            $t->setImage($asset);
            $textModel->save($t);
            var_dump($t->getId());
        }
        var_dump('Ready!');

    }
}