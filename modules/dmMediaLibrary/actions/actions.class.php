<?php

require_once(dmOs::join(sfConfig::get('dm_admin_dir').'/modules/dmMediaLibrary/lib/BasedmMediaLibraryActions.class.php'));

class dmMediaLibraryActions extends BasedmMediaLibraryActions
{
  public function executeMissingMedia(dmWebRequest $request)
  {
    $this->report = $this->getService('mediaTester')->test();
  }
}