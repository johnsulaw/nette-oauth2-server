<?php
declare(strict_types=1);

namespace Lookyman\NetteOAuth2Server\Tests\User;

use Lookyman\NetteOAuth2Server\RedirectConfig;
use Lookyman\NetteOAuth2Server\UI\OAuth2Presenter;
use Lookyman\NetteOAuth2Server\User\LoginSubscriber;
use Nette\Application\Application;
use Nette\Application\UI\Presenter;
use Nette\Security\User;
use PHPUnit\Framework\TestCase;

\DG\BypassFinals::enable();

class LoginSubscriberTest extends TestCase
{

	public function testGetSubscribedEvents(): void
	{
		$subscriber = new LoginSubscriber(
			$this->createMock(RedirectConfig::class),
			10
		);
		self::assertEquals([
			\Contributte\Events\Extra\Event\Application\PresenterEvent::class => 'onPresenter',
			\Contributte\Events\Extra\Event\Security\LoggedInEvent::class => ['onLoggedIn', 10],
		], LoginSubscriber::getSubscribedEvents());
	}

	public function testOnLoggedIn(): void
	{
		$redirectConfig = $this->createMock(RedirectConfig::class);
		$redirectConfig->expects(self::once())->method('getApproveDestination')->willReturn(['destination']);

		$presenter = $this->createMock(Presenter::class);
		$presenter->expects(self::once())->method('getSession')->with(OAuth2Presenter::SESSION_NAMESPACE)->willReturn((object) ['authorizationRequest' => true]);
		$presenter->expects(self::once())->method('redirect')->with('destination');

		$user = $this->createMock(User::class);

		$subscriber = new LoginSubscriber($redirectConfig);
		$subscriber->onPresenter(
			new \Contributte\Events\Extra\Event\Application\PresenterEvent($this->createMock(Application::class), $presenter)
		);
		$subscriber->onLoggedIn(new \Contributte\Events\Extra\Event\Security\LoggedInEvent($user));
	}
	
	public function testOnLoggedInNoPresenter(): void
	{
		$this->expectException(\Nette\InvalidStateException::class);
		$redirectConfig = $this->createMock(RedirectConfig::class);

		$user = $this->createMock(User::class);

		$subscriber = new LoginSubscriber($redirectConfig);
		$subscriber->onLoggedIn(new \Contributte\Events\Extra\Event\Security\LoggedInEvent($user));
	}

}
