<?php

namespace Kunstmaan\MenuBundle\Twig;

use Doctrine\ORM\EntityManager;
use Kunstmaan\MenuBundle\Entity\MenuItem;
use Kunstmaan\MenuBundle\Repository\MenuItemRepositoryInterface;
use Symfony\Component\Routing\RouterInterface;

class MenuTwigExtension extends \Twig_Extension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var MenuItemRepositoryInterface
     */
    private $repository;

    /**
     * @param MenuItemRepositoryInterface $repository
     * @param RouterInterface $router
     */
    public function __construct(MenuItemRepositoryInterface $repository, RouterInterface $router)
    {
	$this->router = $router;
	$this->repository = $repository;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_menu', array($this, 'getMenu'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Get a html representation of a menu.
     *
     * @param string $name
     * @param string $lang
     * @param array $options
     * @return string
     */
    public function getMenu($name, $lang, $options = array())
    {
	/** @var MenuItem $menuRepo */
	$arrayResult = $this->repository->getMenuItemsForLanguage($name, $lang);

	// Make sure the parent item is not offline
	$foundIds = array();
        foreach ($arrayResult as $array) {
            $foundIds[] = $array['id'];
        }
        foreach ($arrayResult as $key => $array) {
	    if (!is_null($array['parent']) && !in_array($array['parent']['id'], $foundIds)) {
                unset($arrayResult[$key]);
            }
        }

	// SET OPTIONS
	// Class for active item
        $activeClass = false;
        if (isset($options['activeClass'])) {
            $activeClass = $options['activeClass'];
        }

        $options = array_merge($this->getDefaultOptions($activeClass), $options);

	$html = $menuRepo->buildTree($arrayResult, $options);

        return $html;
    }

    /**
     * Get the default options to render the html.
     *
     * @return array
     */
    private function getDefaultOptions($activeClass)
    {
        $router = $this->router;

        return array(
            'decorate' => true,
            'rootOpen' => '<ul>',
            'rootClose' => '</ul>',
            'childOpen' => '<li>',
            'childClose' => '</li>',
	    'nodeDecorator' => function ($node) use ($router, $activeClass) {
                $active = false;

                if ($node['type'] == MenuItem::TYPE_PAGE_LINK) {
                    $url = $router->generate('_slug', array('url' => $node['nodeTranslation']['url']));

                    if ($activeClass && $router->getContext()->getPathInfo() == $url) {
                        $active = true;
                    }
                } else {
                    $url = $node['url'];
                }

                if ($node['type'] == MenuItem::TYPE_PAGE_LINK) {
                    if ($node['title']) {
                        $title = $node['title'];
                    } else {
                        $title = $node['nodeTranslation']['title'];
                    }
                } else {
                    $title = $node['title'];
                }

		return '<a href="'.$url.'"'.($active ? ' class="'.$activeClass.'"' : '').($node['newWindow'] ? ' target="_blank"' : '').'>'.$title.'</a>';
	    },
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'kunstmaan_menu_twig_extension';
    }
}
