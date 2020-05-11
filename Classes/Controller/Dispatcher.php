<?php
namespace Typoheads\Formhandler\Controller;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Typoheads\Formhandler\Utility\FlexFormUtility;

/**
 * The Dispatcher instantiates the Component Manager and delegates the process to the given controller.
 */
class Dispatcher extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin
{

    /**
     * Compontent Manager
     *
     * @var \Typoheads\Formhandler\Component\Manager
     */
    protected $componentManager;

    /**
     * The global Formhandler values
     *
     * @var \Typoheads\Formhandler\Utility\Globals
     */
    protected $globals;

    /**
     * The Formhandler utility functions
     *
     * @var \Typoheads\Formhandler\Utility\GeneralUtility
     */
    protected $utilityFuncs;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * Main method of the dispatcher. This method is called as a user function.
     *
     * @return string rendered view
     * @param string $content
     * @param array $setup The TypoScript config
     */
    public function main($content, $setup)
    {
        $this->pi_USER_INT_obj = 1;
        $this->componentManager = GeneralUtility::makeInstance(\Typoheads\Formhandler\Component\Manager::class);
        $this->globals = GeneralUtility::makeInstance(\Typoheads\Formhandler\Utility\Globals::class);
        $this->utilityFuncs = GeneralUtility::makeInstance(\Typoheads\Formhandler\Utility\GeneralUtility::class);
        $this->logger = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class)->getLogger(__CLASS__);
        try {

            //init flexform
            $this->pi_initPIflexForm();

            /*
             * Parse values from flexform:
             * - Template file
             * - Translation file
             * - Predefined form
             * - E-mail settings
             * - Required fields
             * - Redirect page
             */
            $templateFile = FlexFormUtility::fetchFlexformRessource($this->cObj->data['uid'], FlexFormUtility::FETCH_TEMPLATE);
            $langFile = FlexFormUtility::fetchFlexformRessource($this->cObj->data['uid'], FlexFormUtility::FETCH_LANG);
            $predef = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'predefined', 'sDEF');

            $this->globals->setCObj($this->cObj);
            $this->globals->getCObj()->setCurrentVal($predef);
            if ($setup['usePredef']) {
                $predef = $this->utilityFuncs->getSingle($setup, 'usePredef');
            }

            $this->globals->setPredef($predef);
            $this->globals->setOverrideSettings($setup);

            /*
             * set controller:
             * 1. Default controller
             * 2. TypoScript
             */
            $controller = '\Typoheads\Formhandler\Controller\Form';
            if ($setup['controller']) {
                $controller = $setup['controller'];
            }

            /** @var \Typoheads\Formhandler\Controller\AbstractController $controller */
            $controller = $this->componentManager->getComponent($controller);

            if (isset($content)) {
                $controller->setContent($this->componentManager->getComponent($this->utilityFuncs->prepareClassName('Typoheads\Formhandler\Controller\Content'), $content));
            }
            if ($templateFile !== null) {
                $controller->setTemplateFile($templateFile);
            }
            if ($langFile !== null) {
                $controller->setLangFiles([$langFile]);
            }
            if (strlen($predef) > 0) {
                $controller->setPredefined($predef);
            }

            $result = $controller->process();
        } catch (\Exception $e) {
            $this->logger->error(
                $e->getFile() . '(' . $e->getLine() . ')' . ' ' . $e->getMessage(),
                ['formhandler']
            );
            $result = $this->utilityFuncs->getTranslatedMessage($this->globals->getLangFiles(), 'fe-exception');
            if (!$result) {
                $result = '<div style="color:red; font-weight: bold">' . $this->utilityFuncs->getExceptionMessage('fe-exception') . '</div>';
            }
            if ($this->globals->getSession() && $this->globals->getSession()->get('debug')) {
                $result = '<div style="color:red; font-weight: bold">' . $e->getMessage() . '</div>';
                $result .= '<div style="color:red; font-weight: bold">File: ' . $e->getFile() . '(' . $e->getLine() . ')</div>';
                $result .= '<div style="color:red; font-weight: bold">' . $e->getTraceAsString() . '</div>';
            }
        }
        if ($this->globals->getSession() && $this->globals->getSession()->get('debug')) {
            $debuggers = $this->globals->getDebuggers();
            foreach ($debuggers as $idx => $debugger) {
                $debugger->outputDebugLog();
            }
        }
        return $result;
    }
}
