<?php
/**
 * PHP OpenCloud library.
 * 
 * @copyright 2013 Rackspace Hosting, Inc. See LICENSE for information.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 * @author    Glen Campbell <glen.campbell@rackspace.com>
 * @author    Jamie Hannaford <jamie.hannaford@rackspace.com>
 */

namespace OpenCloud\Compute\Resource;

use OpenCloud\Common\PersistentObject;
use OpenCloud\Common\Lang;
use OpenCloud\Common\Exceptions;
use OpenCloud\Compute\Service;
use OpenCloud\Compute\Constants\Network as NetworkConst;

/**
 * The Network class represents a single virtual network
 */
class Network extends PersistentObject 
{

    public $id;
    public $label;
    public $cidr;
    
    protected static $json_name = 'network';
    protected static $url_resource = 'os-networksv2';

    /**
     * Creates a new isolated Network object
     *
     * NOTE: contains hacks to recognize the Rackspace public and private
     * networks. These are not really networks, but they show up in lists.
     *
     * @param \OpenCloud\Compute $service The compute service associated with
     *      the network
     * @param string $id The ID of the network (this handles the pseudo-networks
     *      Network::RAX_PUBLIC and Network::RAX_PRIVATE
     * @return void
     */
    public function __construct(Service $service, $id = null) 
    {
        $this->id = $id;

        switch ($id) {
            case NetworkConst::RAX_PUBLIC:
                $this->label = 'public';
                $this->cidr = 'NA';
                break;
            case NetworkConst::RAX_PRIVATE:
                $this->label = 'private';
                $this->cidr = 'NA';
                break;
            default:
                return parent::__construct($service, $id);
        }
        
        return;
    }

    /**
     * Always throws an error; updates are not permitted
     *
     * @throws NetworkUpdateError always
     */
    public function update($params = array()) 
    {
        throw new Exceptions\NetworkUpdateError('Isolated networks cannot be updated');
    }

    /**
     * Deletes an isolated network
     *
     * @api
     * @return \OpenCloud\HttpResponse
     * @throws NetworkDeleteError if HTTP status is not Success
     */
    public function delete() 
    {
        switch ($this->id) {
            case NetworkConst::RAX_PUBLIC:
            case NetworkConst::RAX_PRIVATE:
                throw new Exceptions\DeleteError('Network may not be deleted');
            default:
                return parent::delete();
        }
    }
    
    /**
     * returns the visible name (label) of the network
     *
     * @api
     * @return string
     */
    public function name() 
    {
        return $this->label;
    }

    /**
     * Creates the JSON object for the Create() method
     */
    protected function createJson() 
    {
        return (object) array(
            'network' => (object) array(
                'cidr'  => $this->cidr,
                'label' => $this->label
            )
        );
    }

}
