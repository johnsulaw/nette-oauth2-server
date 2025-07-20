<?php
declare(strict_types=1);

namespace Lookyman\NetteOAuth2Server\User;

use Lookyman\NetteOAuth2Server\RedirectConfig;
use Lookyman\NetteOAuth2Server\UI\OAuth2Presenter;
use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use Nette\InvalidStateException;
use Nette\Security\User;

class LoginSubscriber implements \Contributte\EventDispatcher\EventSubscriber
{

	/**
	 * @var IPresenter|null
	 */
	private $presenter;

	/**
	 * @var int
	 */
	private $priority;

	/**
	 * @var RedirectConfig
	 */
	private $redirectConfig;

	public function __construct(RedirectConfig $redirectConfig, int $priority = 0)
	{
		$this->redirectConfig = $redirectConfig;
		$this->priority = $priority;
	}

	public function onPresenter(\Contributte\Events\Extra\Event\Application\PresenterEvent $presenterEvent): void
	{
		$this->presenter = $presenterEvent->getPresenter();
	}

	public function onLoggedIn(\Contributte\Events\Extra\Event\Security\LoggedInEvent $loggedInEvent): void
	{
		if ($this->presenter === null) {
			throw new InvalidStateException('Presenter not set');
		}
		if ($this->presenter instanceof Presenter && $this->presenter->getSession(OAuth2Presenter::SESSION_NAMESPACE)->authorizationRequest) {
			$this->presenter->redirect(...$this->redirectConfig->getApproveDestination());
		}
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			\Contributte\Events\Extra\Event\Application\PresenterEvent::class => 'onPresenter',
			\Contributte\Events\Extra\Event\Security\LoggedInEvent::class => ['onLoggedIn', 10],
		];
	}

}
