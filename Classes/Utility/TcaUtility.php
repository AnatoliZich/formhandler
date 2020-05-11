<?php
namespace Typoheads\Formhandler\Utility;

/***************************************************************
     *  Copyright notice
     *
     *  (c) 2010 Dev-Team Typoheads (dev@typoheads.at)
     *  All rights reserved
     *
     *  This script is part of the TYPO3 project. The TYPO3 project is
     *  free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 2 of the License, or
     *  (at your option) any later version.
     *
     *  The GNU General Public License can be found at
     *  http://www.gnu.org/copyleft/gpl.html.
     *
     *  This script is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU General Public License for more details.
     *
     *  This copyright notice MUST APPEAR in all copies of the script!
     ***************************************************************/

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 * UserFunc for rendering of log entry
 */
class TcaUtility
{
    public function getParams($PA, $fobj)
    {
        $params = unserialize($PA['itemFormElValue']);
        $output =
            '<input
			readonly="readonly" style="display:none"
			name="' . $PA['itemFormElName'] . '"
			value="' . htmlspecialchars($PA['itemFormElValue']) . '"
			onchange="' . htmlspecialchars(implode('', $PA['fieldChangeFunc'])) . '"
			' . $PA['onFocus'] . '/>
		';
        $output .= \TYPO3\CMS\Core\Utility\DebugUtility::viewArray($params);
        return $output;
    }

    /**
     * Sets the items for the "Predefined" dropdown.
     *
     * @param array $config
     * @return array The config including the items for the dropdown
     */
    public function addFields_predefined($config)
    {
        $pid = false;

         if (is_array($config['row']) && static::isNewMode($config['row'])) {
            $pid = $this->getCurrentPid($config);

            // the pid currently is negative but it can be fetched as the tt_content element already exists
            if (intval($pid) < 0) {
                $conn = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getConnectionForTable('tt_content');
                $pid = $conn->select(['pid'], 'tt_content', ['uid' => abs($pid)])->fetchColumn(0);
            }
        }

        $contentUid = $config['row']['uid'] ?: 0;
        if (!$pid) {
            $conn = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('tt_content');
            $row = $conn->select(['pid'], 'tt_content', ['uid' => $contentUid])->fetch();
            if ($row) {
                $pid = $row['pid'];
            }
        }
        $ts = $this->loadTS($pid);

        $predef = [];

        // no config available
        if (!is_array($ts['plugin.']['Tx_Formhandler.']['settings.']['predef.']) || count($ts['plugin.']['Tx_Formhandler.']['settings.']['predef.']) === 0) {
            $optionList[] = [
                0 => $GLOBALS['LANG']->sL('LLL:EXT:formhandler/Resources/Private/Language/locallang_db.xml:be_missing_config'),
                1 => ''
            ];
            return $config['items'] = array_merge($config['items'], $optionList);
        }

        // for each view
        foreach ($ts['plugin.']['Tx_Formhandler.']['settings.']['predef.'] as $key => $view) {
            if (is_array($view)) {
                $beName = $view['name'];
                if (isset($view['name.']['data'])) {
                    $data = explode(':', $view['name.']['data']);
                    if (strtolower($data[0]) === 'lll') {
                        array_shift($data);
                    }
                    $langFileAndKey = implode(':', $data);
                    $beName = $GLOBALS['LANG']->sL('LLL:' . $langFileAndKey);
                }
                if (!$predef[$key]) {
                    $predef[$key] = $beName;
                }
            }
        }

        $optionList = [
            [
                0 => $GLOBALS['LANG']->sL('LLL:EXT:formhandler/Resources/Private/Language/locallang_db.xml:be_please_select'),
                1 => ''
            ]
        ];
        foreach ($predef as $k => $v) {
            $optionList[] = [
                0 => $v,
                1 => $k
            ];
        }
        $config['items'] = array_merge($config['items'], $optionList);
        return $config;
    }

    /**
     * Loads the TypoScript for the current page
     *
     * @param int $pageUid
     * @return array The TypoScript setup
     */
    public function loadTS($pageUid)
    {
        $rootLine = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        $TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtendedTemplateService::class);
        $TSObj->tt_track = false;
        $TSObj->runThroughTemplates($rootLine);
        $TSObj->generateConfig();
        return $TSObj->setup;
    }

    /**
     * Returns true when the current content element is not saved yet.
     * To do that, the uid is analyzed. Unsaved rows will have a uid like NEW94835728943759089345
     *
     * @param string|int $uid
     * @return bool
     */
    public static function isNewMode($uid): bool
    {
        return !\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid) || (int)$uid < 0;
    }

    /**
     * Mainly used for compatibility reasons. TYPO3 10 serves the pid differently than earlier versions
     *
     * @param array $config
     * @return int
     */
    protected function getCurrentPid($config)
    {
        return (int)($config['flexParentDatabaseRow']['pid'] ?? $config['row']['pid']);
    }
}
