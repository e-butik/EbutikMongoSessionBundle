<?php

namespace Ebutik\MongoSessionBundle\SessionStorage;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

use Doctrine\ODM\MongoDB\DocumentManager;

use Ebutik\MongoSessionBundle\Escaper\EscaperInterface;
use Ebutik\MongoSessionBundle\Document\Session;

class ODMSessionStorage implements SessionStorageInterface
{
    protected $name;
    protected $serialized_bags = array();
    protected $embedded_bags = array();
    protected $metadata_bag;

    /**
     * @var integer|boolean
     *
     * Null represents unset, false represents set, but with no session id
     */
    protected $intercepted_session_id;

    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var EscaperInterface
     */
    protected $key_escaper;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Session|null
     */
    protected $session = null;

    /**
     * @author Magnus Nordlander
     **/
    public function __construct(DocumentManager $dm, EscaperInterface $key_escaper)
    {
        $this->dm = $dm;
        $this->key_escaper = $key_escaper;
        $this->name = 'SFSESS';
    }

    public function setRequest(Request $request = null)
    {
        if ($this->intercepted_session_id === null && $request) {
            $session_id = $request->cookies->get($this->getName(), false);
            $this->intercepted_session_id = $session_id;
        }
    }

    public function start()
    {
        if ($this->intercepted_session_id === null) {
            throw new \RuntimeException("No session id set");
        }

        if ($this->isStarted()) {
            throw new \RuntimeException("Session is already started");
        }

        if ($this->intercepted_session_id === false) {
            $this->session = new Session();
        } else {
            $this->session = $this->dm
                ->getRepository('EbutikMongoSessionBundle:Session')
                ->find($this->intercepted_session_id);
        }

        $this->metadata_bag = $this->session->getMetadataBagSnapshot();
    }

    /**
     * Checks if the session is started.
     *
     * @return boolean True if started, false otherwise.
     */
    public function isStarted()
    {
        return $this->session !== null;
    }

    /**
     * Returns the session ID
     *
     * @return string The session ID or empty.
     *
     * @api
     */
    public function getId()
    {
        if (!$this->isStarted()) {
            return "";
        }

        return $this->session->getId();
    }

    /**
     * Sets the session ID
     *
     * @param string $id
     *
     * @api
     */
    public function setId($id)
    {
        throw new \LogicException("setId is not supported in ODMSessionStorage");
    }

    /**
     * Returns the session name
     *
     * @return mixed The session name.
     *
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the session name
     *
     * @param string $name
     *
     * @api
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Regenerates id that represents this storage.
     *
     * This method must invoke session_regenerate_id($destroy) unless
     * this interface is used for a storage object designed for unit
     * or functional testing where a real PHP session would interfere
     * with testing.
     *
     * Note regenerate+destroy should not clear the session data in memory
     * only delete the session data from persistent storage.
     *
     * @param Boolean $destroy  Destroy session when regenerating?
     * @param integer $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                          will leave the system settings unchanged, 0 sets the cookie
     *                          to expire with browser session. Time is in seconds, and is
     *                          not a Unix timestamp.
     *
     * @return Boolean True if session regenerated, false if error
     *
     * @throws \RuntimeException If an error occurs while regenerating this storage
     *
     * @api
     */
    public function regenerate($destroy = false, $lifetime = null)
    {
        throw new \RuntimeException("Bar");
    }

    /**
     * Force the session to be saved and closed.
     *
     * This method must invoke session_write_close() unless this interface is
     * used for a storage object design for unit or functional testing where
     * a real PHP session would interfere with testing, in which case it
     * it should actually persist the session data if required.
     *
     * @throws \RuntimeException If the session is saved without being started, or if the session
     *                           is already closed.
     */
    public function save()
    {
        if ($this->session)
        {
            $this->session->setMetadataBagSnapshot($this->metadata_bag);

            $this->dm->persist($this->session);
            $this->dm->flush();
        }
    }

    /**
     * Clear all session data in memory.
     */
    public function clear()
    {
        foreach ($this->getAllBags() as $bag) {
            $bag->clear();
        }
    }

    public function getAllBags()
    {
        return array_merge($this->embedded_bags, $this->serialized_bags);
    }

    /**
     * Gets a SessionBagInterface by name.
     *
     * @param string $name
     *
     * @return SessionBagInterface
     *
     * @throws \InvalidArgumentException If the bag does not exist
     */
    public function getBag($name)
    {
        if (isset($this->embedded_bags[$name])) {
            return $this->embedded_bags[$name];
        } elseif (isset($this->serialized_bags[$name])) {
            return $this->serialized_bags[$name];
        }

        throw new \InvalidArgumentException("The bag does not exist");
    }

    public function hasBag($name)
    {
        if (isset($this->embedded_bags[$name])) {
            return true;
        } elseif (isset($this->serialized_bags[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Registers a SessionBagInterface for use.
     *
     * @param SessionBagInterface $bag
     */
    public function registerBag(SessionBagInterface $bag)
    {
        if ($this->hasBag($bag->getName())) {
            throw new \InvalidArgumentException(sprintf("Session storage already has bag named %s", $bag->getName()));
        }

        if ($bag instanceOf EmbeddedBag) {
            $this->embedded_bags[$bag->getName()] = $bag;
        } else {
            $this->serialized_bags[$bag->getName()] = $bag;
        }
    }

    /**
     * @return MetadataBag
     */
    public function getMetadataBag()
    {
        return $this->metadata_bag;
    }
}