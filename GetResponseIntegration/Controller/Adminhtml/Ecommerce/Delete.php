<?php

namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Helper\Config;
use GetResponse\GetResponseIntegration\Helper\Message;
use Magento\Backend\App\Action;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryValidator;
use Magento\Backend\App\Action\Context;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Repository as GrRepository;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class Delete
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Ecommerce
 */
class Delete extends Action
{
    const BACK_URL = 'getresponse/ecommerce/index';

    /** @var GrRepository */
    private $grRepository;

    /** @var RepositoryValidator */
    private $repositoryValidator;

    /**
     * @param Context $context
     * @param RepositoryFactory $repositoryFactory
     * @param RepositoryValidator $repositoryValidator
     * @throws RepositoryException
     */
    public function __construct(
        Context $context,
        RepositoryFactory $repositoryFactory,
        RepositoryValidator $repositoryValidator
    ) {
        parent::__construct($context);
        $this->grRepository = $repositoryFactory->createRepository();
        $this->repositoryValidator = $repositoryValidator;
    }

    /**
     * @return ResponseInterface|Redirect
     */
    public function execute()
    {
        if (!$this->repositoryValidator->validate()) {
            $this->messageManager->addErrorMessage(Message::INCORRECT_API_RESPONSE_MESSAGE);

            return $this->_redirect(Config::PLUGIN_MAIN_PAGE);
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            $this->messageManager->addErrorMessage(Message::INCORRECT_SHOP);
            $resultRedirect->setPath(self::BACK_URL);

            return $resultRedirect;
        }

        $response = $this->grRepository->deleteShop($id);

        if (isset($response->httpStatus) && $response->httpStatus > 204) {
            $this->messageManager->addErrorMessage(Message::DELETE_SHOP_ERROR . ' - uuid: ' . $response->uuid);
        } else {
            $this->messageManager->addSuccessMessage(Message::STORE_REMOVED);
        }

        $resultRedirect->setPath(self::BACK_URL);

        return $resultRedirect;
    }
}
