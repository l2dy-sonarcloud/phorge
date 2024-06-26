<?php

final class PhabricatorCalendarInviteeViewerFunctionDatasource
  extends PhabricatorTypeaheadDatasource {

  public function getBrowseTitle() {
    return pht('Browse Viewer');
  }

  public function getPlaceholderText() {
    return pht('Type viewer()...');
  }

  public function getDatasourceApplicationClass() {
    return PhabricatorPeopleApplication::class;
  }

  public function getDatasourceFunctions() {
    return array(
      'viewer' => array(
        'name' => pht('Current Viewer'),
        'summary' => pht('Use the current viewing user.'),
        'description' => pht(
          'Show invites the current viewer is invited to. This function '.
          'includes events the user is invited to because a project they '.
          'are a member of is invited.'),
      ),
    );
  }

  protected function isFunctionWithLoginRequired($function) {
    return true;
  }

  public function loadResults() {
    if ($this->getViewer()->isLoggedIn()) {
      $results = array($this->renderViewerFunctionToken());
    } else {
      $results = array();
    }

    return $this->filterResultsAgainstTokens($results);
  }

  protected function canEvaluateFunction($function) {
    if (!$this->getViewer()->isLoggedIn()) {
      return false;
    }

    return parent::canEvaluateFunction($function);
  }

  protected function evaluateFunction($function, array $argv_list) {
    $results = array();
    foreach ($argv_list as $argv) {
      $results[] = $this->getViewer()->getPHID();
    }

    return PhabricatorCalendarInviteeDatasource::expandInvitees(
      $this->getViewer(),
      $results);
  }

  public function renderFunctionTokens($function, array $argv_list) {
    $tokens = array();
    foreach ($argv_list as $argv) {
      $tokens[] = PhabricatorTypeaheadTokenView::newFromTypeaheadResult(
        $this->renderViewerFunctionToken());
    }
    return $tokens;
  }

  private function renderViewerFunctionToken() {
    return $this->newFunctionResult()
      ->setName(pht('Current Viewer'))
      ->setPHID('viewer()')
      ->setIcon('fa-user')
      ->setUnique(true);
  }

}
