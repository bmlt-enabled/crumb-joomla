<?php

/**
 * @package         Crumb
 * @copyright   (C) 2026 bmlt-enabled. All rights reserved.
 * @license         GNU General Public License version 2 or later
 */

\defined('_JEXEC') or die;

/**
 * @var \Joomla\Registry\Registry $params
 * @var \BmltEnabled\Module\Crumb\Site\Helper\CrumbHelper $helper
 */

$settings = $helper->getSettings($params);
echo $helper->render($settings);
