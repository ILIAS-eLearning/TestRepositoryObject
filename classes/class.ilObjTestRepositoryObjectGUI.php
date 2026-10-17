<?php

declare(strict_types=1);

use ILIAS\UI\Component\Input\Container\Form\Standard;

/**
 * @ilCtrl_isCalledBy ilObjTestRepositoryObjectGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls      ilObjTestRepositoryObjectGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilExportGUI
 */
class ilObjTestRepositoryObjectGUI extends ilObjectPluginGUI
{
    public const string LP_SESSION_ID = 'xtst_lp_session_state';
    final public function getType() : string
    {
        return ilTestRepositoryObjectPlugin::ID;
    }

    public function performCommand(string $cmd) : void
    {
        switch ($cmd) {
            case 'editProperties':
            case 'saveProperties':
            case 'addNews':
            case 'updateOneNews':
            case 'deleteOneNews':
                $this->checkPermission('write');
                break;
            case 'showContent':
            case 'setStatusToCompleted':
            case 'setStatusToFailed':
            case 'setStatusToInProgress':
            case 'setStatusToNotAttempted':
                $this->checkPermission('read');
                break;
            default:
                $this->error->raiseError($this->lng->txt('unknown_command'), $this->error->WARNING);
                return;
        }

        $this->$cmd();
    }

    public function getAfterCreationCmd() : string
    {
        return 'editProperties';
    }

    public function getStandardCmd() : string
    {
        return 'showContent';
    }

    protected function setTabs() : void
    {
        if ($this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->tabs->addTab('content', $this->txt('content'), $this->ctrl->getLinkTarget($this, 'showContent'));
        }

        $this->addInfoTab();

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs->addTab(
                'properties',
                $this->txt('properties'),
                $this->ctrl->getLinkTarget($this, 'editProperties')
            );
        }

        $this->addExportTab();
        $this->addPermissionTab();
        $this->activateTab();
    }

    protected function editProperties() : void
    {
        $this->tabs->activateTab('properties');
        $this->renderPropertiesForm($this->getPropertiesForm());
    }

    private function getPropertiesForm() : Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormAction($this, 'saveProperties'),
            [
                'title' => $this->ui_factory->input()->field()->text($this->txt('title'))
                    ->withRequired(true)
                    ->withValue($this->object->getTitle()),
                'description' => $this->ui_factory->input()->field()->textarea($this->txt('description'))
                    ->withValue($this->object->getDescription()),
                'online' => $this->ui_factory->input()->field()->checkbox($this->txt('online'))
                    ->withValue($this->getObject()->isOnline()),
            ]
        )->withSubmitLabel($this->txt('update'));
    }

    protected function saveProperties() : void
    {
        $form = $this->getPropertiesForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->renderPropertiesForm($form);
            return;
        }

        $object = $this->getObject();
        $object->setTitle($data['title']);
        $object->setDescription($data['description']);
        $object->setOnline($data['online']);
        $object->update();
        $this->tpl->setOnScreenMessage('success', $this->txt('update_successful'), true);
        $this->ctrl->redirect($this, 'editProperties');
    }

    protected function showContent() : void
    {
        $this->tabs->activateTab('content');
        global $ilUser;

        $progress = new ilLPStatusPlugin($this->object->getId());
        $status = $progress->determineStatus($this->object->getId(), $ilUser->getId());
        $components = [
            $this->ui_factory->panel()->standard(
                $this->object->getTitle(),
                $this->ui_factory->listing()->descriptive([
                    $this->txt('title') => $this->object->getTitle(),
                    $this->txt('description') => $this->object->getDescription(),
                    $this->txt('online_status') => $this->getObject()->isOnline() ? $this->txt('online') : $this->txt('offline'),
                    $this->txt('lp_status') => $this->txt('lp_status_' . $status),
                ])
            ),
            $this->ui_factory->button()->standard(
                $this->txt('set_completed'),
                $this->ctrl->getLinkTarget($this, 'setStatusToCompleted')
            ),
            $this->ui_factory->button()->standard(
                $this->txt('set_not_attempted'),
                $this->ctrl->getLinkTarget($this, 'setStatusToNotAttempted')
            ),
            $this->ui_factory->button()->standard(
                $this->txt('set_failed'),
                $this->ctrl->getLinkTarget($this, 'setStatusToFailed')
            ),
            $this->ui_factory->button()->standard(
                $this->txt('set_in_progress'),
                $this->ctrl->getLinkTarget($this, 'setStatusToInProgress')
            )
        ];

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $components[] = $this->ui_factory->panel()->standard(
                $this->txt('add_news'),
                $this->getNewsForm()
            );
        }

        foreach ($this->getNewsItems() as $news_item) {
            $components[] = $this->ui_factory->panel()->standard(
                $news_item->getTitle(),
                $this->ui_factory->listing()->descriptive([
                    $this->txt('news_content') => $news_item->getContent(),
                ])
            );

            if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
                $components[] = $this->getNewsForm($news_item);
                $components[] = $this->getDeleteNewsForm($news_item);
            }
        }

        $this->tpl->setContent($this->ui_renderer->render($components));
    }

    protected function addNews() : void
    {
        $this->storeNews($this->getNewsForm(), new ilNewsItem());
    }

    protected function updateOneNews() : void
    {
        $news_item = $this->getRequestedNewsItem();
        $this->storeNews($this->getNewsForm($news_item), $news_item);
    }

    protected function deleteOneNews() : void
    {
        $news_item = $this->getRequestedNewsItem();
        $form = $this->getDeleteNewsForm($news_item)->withRequest($this->request);
        if ($form->getData() === null) {
            $this->showContent();
            return;
        }

        $news_item->delete();
        $this->ctrl->redirect($this, $this->getStandardCmd());
    }

    protected function setStatusToCompleted() : void
    {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_COMPLETED_NUM);
    }

    protected function setStatusToFailed() : void
    {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_FAILED_NUM);
    }

    protected function setStatusToInProgress() : void
    {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_IN_PROGRESS_NUM);
    }

    protected function setStatusToNotAttempted() : void
    {
        $this->setStatusAndRedirect(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM);
    }

    private function setStatusAndRedirect(int $status) : void
    {
        global $ilUser;

        $_SESSION[self::LP_SESSION_ID] = $status;
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $ilUser->getId());
        $this->ctrl->redirect($this, $this->getStandardCmd());
    }

    private function getNewsForm(?ilNewsItem $news_item = null) : Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->getNewsFormAction($news_item === null ? 'addNews' : 'updateOneNews', $news_item?->getId()),
            [
                'title' => $this->ui_factory->input()->field()->text($this->txt('news_title'))
                    ->withRequired(true)
                    ->withValue($news_item?->getTitle() ?? ''),
                'content' => $this->ui_factory->input()->field()->textarea($this->txt('news_content'))
                    ->withRequired(true)
                    ->withValue($news_item?->getContent() ?? ''),
            ]
        )->withSubmitLabel($this->txt($news_item === null ? 'save' : 'update'));
    }

    private function getDeleteNewsForm(ilNewsItem $news_item) : Standard
    {
        return $this->ui_factory->input()->container()->form()->standard(
            $this->getNewsFormAction('deleteOneNews', $news_item->getId()),
            []
        )->withSubmitLabel($this->txt('delete'));
    }

    private function getNewsFormAction(string $command, ?int $news_id = null) : string
    {
        if ($news_id !== null) {
            $this->ctrl->setParameter($this, 'news_id', $news_id);
        }
        $action = $this->ctrl->getFormAction($this, $command);
        $this->ctrl->clearParameters($this);

        return $action;
    }

    private function storeNews(Standard $form, ilNewsItem $news_item) : void
    {
        global $ilUser;

        $form = $form->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->showContent();
            return;
        }

        $news_item->setTitle($data['title']);
        $news_item->setContent($data['content']);
        if ($news_item->getId() === 0) {
            $news_item->setContext($this->object->getId(), $this->object->getType());
            $news_item->setUserId($ilUser->getId());
            $news_item->setVisibility(NEWS_USERS);
            $news_item->create();
        } else {
            $news_item->setUpdateUserId($ilUser->getId());
            $news_item->update();
        }

        $this->ctrl->redirect($this, $this->getStandardCmd());
    }

    private function getNewsItems() : array
    {
        return ilNewsItem::getNewsOfContext($this->object->getId(), $this->object->getType());
    }

    private function getRequestedNewsItem() : ilNewsItem
    {
        $news_id = (int) ($this->request->getQueryParams()['news_id'] ?? 0);
        $news_item = new ilNewsItem($news_id);
        if ($news_id <= 0 || $news_item->getContextObjId() !== $this->object->getId()
            || $news_item->getContextObjType() !== $this->object->getType()) {
            throw new ilException('News item does not belong to this repository object.');
        }

        return $news_item;
    }

    private function renderPropertiesForm(Standard $form) : void
    {
        $this->tpl->setContent($this->ui_renderer->render(
            $this->ui_factory->panel()->standard($this->txt('properties'), $form)
        ));
    }

    private function activateTab() : void
    {
        if ($this->ctrl->getCmdClass() === 'ilexportgui') {
            $this->tabs->activateTab('export');
        }
    }

    public function getObject() : ?ilObjTestRepositoryObject
    {
        assert($this->object instanceof ilObjTestRepositoryObject);
        return $this->object;
    }
}