<?php
use_javascript('lib.dataTable');
use_stylesheet('admin.dataTable');

$table = _table('.data_table')->head(
  __('Media Id'),  
  __('Reason'),  
  __('Widget'),
  __('Page')  
);  

foreach($report->getWidgetsWithMissingMedia() as $widgetWithMissing)
{    
  $widget = DmWidgetTable::getInstance()->find($widgetWithMissing['widget']);
  $pageview = $widget->getZone()->getArea()->getPageView();
  $page = DmPageTable::getInstance()->findOneByModuleAndAction($pageview->module, $pageview->action);
  
  $table->body(  
    $widgetWithMissing['id'],
    __($report->getReason($widgetWithMissing['id'])),
    $widget->module . "/" . $widget->action,
    _link($page)
  );  
}
echo _tag('div.dm_data', $table);