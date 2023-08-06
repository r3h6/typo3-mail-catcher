<?php

declare(strict_types=1);

namespace R3H6\MailCatcher\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use R3H6\MailCatcher\Domain\Model\Message;
use R3H6\MailCatcher\Domain\Repository\MessageRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\GenericButton;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

#[Controller]
class MessageController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly MessageRepository $messageRepository,
        protected readonly IconFactory $iconFactory,
        protected readonly EventDispatcherInterface $dispatcher,
        protected readonly string $mailerDsn,
    ) {
    }

    public function indexAction(int $page = 1): ResponseInterface
    {
        $itemsPerPage = 20;
        $messages = $this->messageRepository->findAll();
        $paginator = new QueryResultPaginator($messages, $page, $itemsPerPage);
        $pagination = new SimplePagination($paginator);

        $this->view->assign('forwarding', !empty($this->mailerDsn));
        $this->view->assign('paginator', $paginator);
        $this->view->assign('pagination', $pagination);
        $this->view->assign('total', count($messages));
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());

        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $buttonBar->addButton(
            $buttonBar->makeLinkButton()
                ->setTitle(LocalizationUtility::translate('button.deleteAll', 'MailCatcher'))
                ->setShowLabelText(true)
                ->setHref($this->uriBuilder->reset()->uriFor('deleteAll'))
                ->setIcon($this->iconFactory->getIcon('actions-delete', Icon::SIZE_SMALL)),
            ButtonBar::BUTTON_POSITION_LEFT,
            1
        );

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function showAction(Message $message, string $backLink): ResponseInterface
    {
        /** @var \Symfony\Component\Mime\Email $email */
        $email = unserialize($message->getSerialized());
        $this->view->assign('text', !empty($email->getTextBody()));
        $this->view->assign('html', !empty($email->getHtmlBody()));
        $this->view->assign('attachments', $email->getAttachments());
        $this->view->assign('message', $message);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());

        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $buttonBar->addButton(
            $buttonBar->makeLinkButton()
                ->setTitle(LocalizationUtility::translate('button.back', 'MailCatcher'))
                ->setShowLabelText(true)
                ->setHref($backLink)
                ->setIcon($this->iconFactory->getIcon('actions-close', Icon::SIZE_SMALL)),
            ButtonBar::BUTTON_POSITION_LEFT,
            1
        );
        if (!empty($this->mailerDsn)) {
            $buttonBar->addButton(
                GeneralUtility::makeInstance(GenericButton::class)
                    ->setTag('button')
                    ->setLabel(LocalizationUtility::translate('button.forward', 'MailCatcher'))
                    ->setShowLabelText(true)
                    ->setAttributes(['data-forward-action' => $this->uriBuilder->reset()->uriFor('forward', ['message' => $message])])
                    ->setIcon($this->iconFactory->getIcon('actions-arrow-right', Icon::SIZE_SMALL)),
                ButtonBar::BUTTON_POSITION_RIGHT,
                1
            );
        }

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function previewAction(Message $message, string $partKey): ResponseInterface
    {
        /** @var \Symfony\Component\Mime\Email $email */
        $email = unserialize($message->getSerialized());

        $response = $this->responseFactory->createResponse();

        if ($partKey === 'html') {
            $response = $response->withHeader('Content-Type', 'text/html');
            $response->getBody()->write((string)$email->getHtmlBody());
        }

        if ($partKey === 'text') {
            $response = $response->withHeader('Content-Type', 'text/plain');
            $response->getBody()->write((string)$email->getTextBody());
        }

        return $response;
    }

    public function downloadAction(Message $message, string $attachmentKey): ResponseInterface
    {
        /** @var \Symfony\Component\Mime\Email $email */
        $email = unserialize($message->getSerialized());
        $attachment = $email->getAttachments()[$attachmentKey] ?? null;
        if (!$attachment instanceof \Symfony\Component\Mime\Part\DataPart) {
            return $this->responseFactory->createResponse(Response::HTTP_NOT_FOUND);
        }

        $response = $this->responseFactory->createResponse()
            ->withHeader('Content-Type', $attachment->getContentType())
            ->withHeader('Content-Dispostion', 'attachment; filename="' . ($attachment->getFilename()) . '"');

        $response->getBody()->write($attachment->getBody());
        return $response;
    }

    public function forwardAction(Message $message, string $receiverEmail = '', string $redirectUri = null): ResponseInterface
    {
        /** @var \Symfony\Component\Mime\Email $email */
        $email = unserialize($message->getSerialized());

        $email->setHeaders(new Headers());

        $email->subject($message->getSubject());
        $email->to(new Address($receiverEmail));
        $email->from(MailUtility::getSystemFromAddress());

        $transport = Transport::fromDsn(
            $this->mailerDsn,
            $this->dispatcher,
            null,
            GeneralUtility::makeInstance(LogManager::class)->getLogger(Transport::class)
        );

        $transport->send($email);
        $this->addFlashMessage(LocalizationUtility::translate('flashMessage.forwarded', 'MailCatcher', [$receiverEmail]));

        if ($redirectUri) {
            return new RedirectResponse($redirectUri);
        }

        return $this->redirect('index');
    }

    public function deleteAllAction(): ResponseInterface
    {
        $this->messageRepository->deleteAll();
        return $this->redirect('index');
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
