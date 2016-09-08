<?php
namespace Mouf\Security\RightsService\Twig;

use Mouf\Security\RightsService\RightsServiceInterface;

class RightsServiceTwigExtension extends \Twig_Extension
{
    /**
     * @var RightsServiceInterface
     */
    private $rightsService;

    /**
     * @param RightsServiceInterface $rightsService
     */
    public function __construct(RightsServiceInterface $rightsService)
    {
        $this->rightsService = $rightsService;
    }

    /**
     * (non-PHPdoc)
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'rights_service';
    }

    public function getFunctions()
    {
        return array(
            /**
             * The is_allowed Twig function checks if the current used has a given right.
             */
            new \Twig_SimpleFunction('is_allowed', [$this, 'isAllowed']),
        );
    }

    public function isAllowed($right, $scope = null)
    {
        return $this->rightsService->isAllowed($right, $scope);
    }
}