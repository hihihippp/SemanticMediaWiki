<?php

namespace SMW\Test;

use SMW\CacheIdGenerator;
use SMW\CacheHandler;

use HashBagOStuff;

/**
 * @covers \SMW\CacheHandler
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 *
 * @licence GNU GPL v2+
 * @since 1.9
 *
 * @author mwjames
 */
class CacheHandlerTest extends SemanticMediaWikiTestCase {

	/**
	 * @return string
	 */
	public function getClass() {
		return '\SMW\CacheHandler';
	}

	/**
	 * @note HashBagOStuff is used as test interface because it stores
	 * content in an associative array (which is not going to persist)
	 *
	 * @return CacheHandler
	 */
	private function getInstance() {
		return new CacheHandler( new HashBagOStuff );
	}

	/**
	 * @since 1.9
	 */
	public function testConstructor() {
		$this->assertInstanceOf( $this->getClass(), $this->getInstance() );
		$this->assertInstanceOf( 'BagOStuff', $this->getInstance()->getCache() );
	}

	/**
	 * @since 1.9
	 */
	public function testNewFromId() {

		CacheHandler::reset();

		// Invoke a valid cacheId
		$instance = CacheHandler::newFromId( 'hash' );
		$this->assertFalse( $instance->isEnabled() ); // No key means false
		$instance->setCacheEnabled( true )->key( 'lala' );
		$this->assertTrue( $instance->isEnabled() ); // An added key results in true

		// Static
		$this->assertTrue( $instance === CacheHandler::newFromId( 'hash' ) );

		// Reset
		$instance->reset();
		$this->assertTrue( $instance !== CacheHandler::newFromId( 'hash' ) );

		// Invoke an invalid cacheId
		$instance = CacheHandler::newFromId( 'lula' );
		$this->assertFalse( $instance->isEnabled() ); // No key means false
		$instance->setCacheEnabled( true )->key( 'lila' );
		$this->assertFalse( $instance->isEnabled() ); // An added key but invalid cache results in false

		// Static
		$this->assertTrue( $instance === CacheHandler::newFromId( 'lula' ) );

		// Reset
		$instance->reset();
		$this->assertTrue( $instance !== CacheHandler::newFromId( 'lula' ) );
	}

	/**
	 * @dataProvider keyItemDataProvider
	 *
	 * @since 1.9
	 */
	public function testEnabledCache( $key, $item ) {

		$instance = $this->getInstance();

		// Assert key handling
		$instance->setCacheEnabled( true )->key( $key );
		$instanceKey = $instance->getKey();

		// Assert storage and retrieval
		$instance->set( $item );
		$this->assertEquals( $item, $instance->get() );

		// Assert deletion
		$instance->delete();

		$this->assertEmpty( $instance->get() );
		$this->assertEquals( $instanceKey, $instance->getKey() );

		// Set key
		$instance->setCacheEnabled( true )->setKey( new CacheIdGenerator( $key, 'test-prefix' ) );
		$this->assertContains( 'test-prefix' , $instance->getKey() );

	}

	/**
	 * @dataProvider keyItemDataProvider
	 *
	 * @since 1.9
	 */
	public function testDisabledCache( $key, $item ) {

		$instance = $this->getInstance();

		// Assert key handling
		$instance->setCacheEnabled( false )->key( $key );
		$instanceKey = $instance->getKey();

		// Assert storage and retrieval
		$instance->set( $item );
		$this->assertEmpty( $instance->get() );

		// Assert deletion
		$instance->delete();

		$this->assertEmpty( $instance->get() );
		$this->assertEquals( $instanceKey, $instance->getKey() );
	}

	/**
	 * @return array
	 */
	public function keyItemDataProvider() {

		// Generates a random key
		$key = $this->newRandomString( 10 );

		// Generates a random text object
		$item = array(
			$this->newRandomString( 10 ),
			$this->newRandomString( 20 )
		);

		return array( array( $key, $item ) );
	}
}
