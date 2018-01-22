<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\User;
use App\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class AllTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Session::start();

        Artisan::call('migrate:rollback');
        Artisan::call('migrate');

        $user = factory(User::class)->create([
            'name' => 'John Doe',
            'email' => 'monxmon@gmail.com'
        ]);
    }

    public function testIndexPage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitle('Laravel')
                    ->assertSee('Welcome To Laravel!');
        });
    }

    public function testHomePageLoginBtn()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                    ->visit($browser->attribute('#login-homepage', 'href'))
                    ->assertPathIs('/login');
        });
    }

    public function testHomePageRegisterBtn()
    {
        $this->browse(function ($browser) {
            $browser->visit('/')
                    ->visit($browser->attribute('#register-homepage', 'href'))
                    ->assertPathIs('/register');
        });
    }

    public function testRegisterUser()
    {
        $this->browse(function ($browser) {
            $browser->visit('/register')
                    ->type('name', 'Karolina Nicco')
                    ->type('email', 'kani@gmail.com')
                    ->type('password', 'secret')
                    ->type('password_confirmation', 'secret')
                    ->press('Register')
                    ->pause('3000')
                    ->assertPathIs('/home')
                    ->assertSee('Your Blog Posts')
                    ->pause('2000')
                    ->logout();
        });
    }

    public function testLoginUser()
    {
       $this->browse(function ($browser) {
            $browser->visit('/login')
                    ->type('email','monxmon@gmail.com')
                    ->type('password','secret')
                    ->press('Login')
                    ->assertPathIs('/home')
                    ->assertSee('Your Blog Posts')
                    ->pause('3000')
                    ->logout();
        });
    }

    public function testLoginUserWithUnregisteredEmail()
    {
        $this->browse(function ($browser) {
            $browser->visit('/login')
                    ->type('email','mon@gmail.com')
                    ->type('password','secret')
                    ->press('Login')
                    ->pause('3000')
                    ->assertSee('These credentials do not match our records.');
        });
    }

    public function testCreatePost()
    {
        $this->browse(function ($browser) {
            $browser->visit('/login')
                    ->type('email','monxmon@gmail.com')
                    ->type('password','secret')
                    ->press('Login')
                    ->assertPathIs('/home')
                    ->visit($browser->attribute('#create-post', 'href'))
                    ->assertPathIs('/posts/create')
                    ->pause('3000')
                    ->type('title','Title')
                    ->type('body','Body here.')
                    ->press('Submit')
                    ->logout();
        });
    }

    public function testUpdatePost()
    {
        $this->browse(function ($browser) {
            $browser->visit('/login')
                    ->type('email','monxmon@gmail.com')
                    ->type('password','secret')
                    ->press('Login')
                    ->assertPathIs('/home')
                    ->assertSee('Your Blog Posts')
                    ->pause('1500')
                    ->visit($browser->attribute('#create-post', 'href'))
                    ->assertPathIs('/posts/create')
                    ->pause('1500')
                    ->type('title','Test Title')
                    ->type('body','Test body here.')
                    ->press('Submit')
                    ->visit('/home')
                    ->visit($browser->attribute('#edit-post', 'href'))
                    ->assertPathIs('/posts/1/edit')
                    ->type('title','Final update title')
                    ->type('body','Final update body here.')
                    ->press('Submit')
                    ->logout();
        });
    }

    public function testDeletePost()
    {
        $this->browse(function ($browser) {
            $browser->visit('/login')
                    ->type('email','monxmon@gmail.com')
                    ->type('password','secret')
                    ->press('Login')
                    ->assertPathIs('/home')
                    ->assertSee('Your Blog Posts')
                    ->pause('1500')
                    ->visit($browser->attribute('#create-post', 'href'))
                    ->assertPathIs('/posts/create')
                    ->pause('1500')
                    ->type('title','Test Title')
                    ->type('body','Test body here.')
                    ->press('Submit')
                    ->visit('/home')
                    ->press('Delete')
                    ->assertPathIs('/posts')
                    ->assertSee('Post Removed')
                    ->logout();
        });
    }

    public function testDatabase()
    {
        $this->assertDatabaseHas('users', ['email' => 'monxmon@gmail.com']);
    }

    public function testApplicationNotDown()
    {
        $response = $this->call('GET', '/');

        $this->assertEquals(200, $response->status());
    }

    public function testApplicationNotDownOtherMethod() 
    {
        $this->get('/')
             ->assertSuccessful();
    }
}
