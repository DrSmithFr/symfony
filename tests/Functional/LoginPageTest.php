<?php
// tests/Functional/LoginPageTest.php
namespace App\Tests\Functional;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\PantherTestCase;

class LoginPageTest extends PantherTestCase
{
    /**
     * @throws NoSuchElementException
     */
    public function testLoginPageDisplaysForm()
    {
        $client = self::createPantherClient();

        // Rendez‑vous sur la page de login
        $client->request('GET', '/account/login');

        // On attend que le titre soit présent
        $this->assertSelectorTextContains('h1', 'Welcome');

        // Vérifier les champs du formulaire
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        $this->assertSelectorExists('button[type="submit"]');

        // On vérifie que le bouton de soumission est bien visible
        $submit = $client->findElement(WebDriverBy::cssSelector('button[type="submit"]'));
        $this->assertTrue($submit->isDisplayed());
    }
}
