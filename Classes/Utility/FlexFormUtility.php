<?php

namespace Typoheads\Formhandler\Utility;

use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;

class FlexFormUtility
{
    const FETCH_TEMPLATE = 'pi_flexform_templ';

    const FETCH_LANG = 'pi_flexform_lang';

    /**
     * @param int|string $ttContentUid The tt_content uid where the formhandler plugin resides
     * @param string $fetchMode see constants in FalUtility class for possible values
     *
     * @return string
     */
    public static function fetchFlexformRessource($ttContentUid, $fetchMode = self::FETCH_TEMPLATE): ?string
    {
        $templateFile = null;

        $fileObjects = static::fetchAllFalResources('tt_content', $fetchMode, $ttContentUid);

        if (!empty($fileObjects)) {
            /** @var FileReference $templateFile */
            $fileResource = $fileObjects[0];
            $templateFile = $fileResource->getPublicUrl();
        }

        return $templateFile;
    }

    /**
     * @param string $table
     * @param string $fieldName
     * @param int|string $uid
     * @return array
     */
    public static function fetchAllFalResources($table, $fieldName, $uid)
    {
        /** @var FileRepository $fileRepository */
        $fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FileRepository::class);
        return $fileRepository->findByRelation($table, $fieldName, $uid);
    }
}
