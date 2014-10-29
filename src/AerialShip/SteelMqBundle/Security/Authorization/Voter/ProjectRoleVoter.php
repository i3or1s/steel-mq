<?php

namespace AerialShip\SteelMqBundle\Security\Authorization\Voter;

use AerialShip\SteelMqBundle\Entity\Project;
use AerialShip\SteelMqBundle\Entity\ProjectRole;
use AerialShip\SteelMqBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class ProjectRoleVoter implements VoterInterface
{
    /** @var RoleHierarchyInterface */
    protected $roleHierarchy;

    /**
     * @param RoleHierarchyInterface $roleHierarchy
     */
    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool    true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return ProjectRole::isRoleValid($attribute);
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool    true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, 'AerialShip\SteelMqBundle\Entity\Project');
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param object|null $object The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return int     either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (false == $object || false == $this->supportsClass(get_class($object))) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        /** @var Project $project */
        $project = $object;
        $user = $this->getUser($token);

        $projectRole = $this->getProjectRole($user, $project);

        if (false == $projectRole) {
            return VoterInterface::ACCESS_DENIED;
        }

        $roles = $this->extractRoles($projectRole);

        foreach ($attributes as $attribute) {
            $attribute = strtoupper($attribute);
            foreach ($roles as $role) {
                if ($attribute == $role->getRole()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * @param User $user
     * @param Project $project
     * @return ProjectRole|null
     */
    protected function getProjectRole(User $user, Project $project)
    {
        foreach ($user->getProjectRoles() as $projectRole) {
            if ($projectRole->getProject()->getId() == $project->getId()) {
                return $projectRole;
            }
        }

        return null;
    }

    /**
     * @param TokenInterface $token
     * @return User
     */
    protected function getUser(TokenInterface $token)
    {
        return $token->getUser();
    }

    /**
     * @param ProjectRole $projectRole
     * @return Role[]
     */
    protected function extractRoles(ProjectRole $projectRole)
    {
        $roles = array();
        foreach ($projectRole->getRoles() as $roleName) {
            $roles[] = new Role(strtoupper($roleName));
        }

        return $this->roleHierarchy->getReachableRoles($roles);
    }
}