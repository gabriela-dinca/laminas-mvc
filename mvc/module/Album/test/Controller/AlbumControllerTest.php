<?php

namespace AlbumTest\Controller;

use Album\Controller\AlbumController;
use Album\Model\Album;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Album\Model\AlbumTable;
use Laminas\ServiceManager\ServiceManager;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class AlbumControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    protected $albumTable;
    protected function setUp(): void
    {
        $configOverrides = [];
        $this->setApplicationConfig(ArrayUtils::merge(// Grabbing the full application configuration:
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));
        parent::setUp();
        $this->configureServiceManager($this->getApplicationServiceLocator());
    }

    protected function configureServiceManager(ServiceManager $services)
    {
        $services->setAllowOverride(true);
        $services->setService('config', $this->updateConfig($services->get('config')));
        $services->setService(AlbumTable::class, $this->mockAlbumTable()->reveal());
        $services->setAllowOverride(false);
    }

    protected function updateConfig($config)
    {
        $config['db'] = [];
        return $config;
    }

    protected function mockAlbumTable(): ObjectProphecy
    {
        $this->albumTable = $this->prophesize(AlbumTable::class);
        return $this->albumTable;
    }

    /**
     * @throws \Exception
     */
    public function testIndexActionCanBeAccessed(): void
    {
        $this->albumTable->fetchAll()->willReturn([]);
        $this->dispatch('/album');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Album');
        $this->assertControllerName(AlbumController::class);
        $this->assertControllerClass('AlbumController');
        $this->assertMatchedRouteName('album');
    }

    /**
     * @throws \Exception
     */
    public function testAddActionRedirectsAfterValidPost(): void
    {
        $this->albumTable
            ->saveAlbum(Argument::type(Album::class))
            ->shouldBeCalled();
        $postData = [
            'title'  => 'Led Zeppelin III',
            'artist' => 'Led Zeppelin',
            'id'     => '',
        ];
        $this->dispatch('/album/add', 'POST', $postData);
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/album');
    }

//        todo $this->albumTable->getAlbum($id)->willReturn(new Album());
//        Test that a non-POST request to addAction() displays an empty form.
//        Test that an invalid data provided to addAction() re-displays the form, but with error messages.
//        Test that absence of an identifier in the route parameters when invoking either editAction() or deleteAction() will redirect to the appropriate location.
//        Test that an invalid identifier passed to editAction() will redirect to the album landing page.
//        Test that non-POST requests to editAction() and deleteAction() display forms.
}
