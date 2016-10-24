<?php

namespace FL\GmailDoctrineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class Credentials
{
    /**
     * @ORM\Id
     * @ORM\Column(type="id", nullable=false, unique=true)
     * @var int|null
     */
    protected $id;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @var array|null
     */
    protected $tokenArray;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    protected $authCode;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array|null
     */
    public function getTokenArray()
    {
        return $this->tokenArray;
    }

    /**
     * @param array|null $tokenArray
     * @return Credentials
     */
    public function setTokenArray(array $tokenArray)
    {
        $this->tokenArray = $tokenArray;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @param string|null $authCode
     * @return Credentials
     */
    public function setAuthCode(string $authCode = null)
    {
        $this->authCode = $authCode;

        return $this;
    }
}
