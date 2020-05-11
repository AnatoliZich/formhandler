<?php

namespace Typoheads\Formhandler\Utility;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class FlexFormJsLoader extends AbstractFormElement
{
    /**
     * @return array|string
     */
    public function render()
    {
        // Custom TCA properties and other data can be found in $this->data, for example the above
        // parameters are available in $this->data['parameterArray']['fieldConf']['config']['parameters']
        $result = $this->initializeResultArray();

        $uid = $this->data['databaseRow']['uid'];
        $newRecord = TcaUtility::isNewMode($uid);

        $js = "<script>\n";
        $js .= "/*<![CDATA[*/\n";

        $js .= "var uid = '" . $uid . "';\n";
        $js .= "var flexformBoxId = 'DIV.c-tablayer';\n";
        $js .= 'var newRecord = ' . $newRecord . ";\n";
        $js .= file_get_contents(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('formhandler') . 'Resources/Public/JavaScript/addFields_predefinedJS.js');
        $js .= "/*]]>*/\n";
        $js .= "</script>\n";

        $result['html'] = $js;
        return $result;
    }
}
