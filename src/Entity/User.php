<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\UserInterface;

//use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User implements UserInterface
{
//  const ROLE_DEFAULT = 'ROLE_USER';
//
//  const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

  //-----------------------------------------
  //-----------------------------------------
  //
  // SONATA BASE USER
  //
  //-----------------------------------------
  //-----------------------------------------

  /**
   * Hook on pre-persist operations.
   */
  public function prePersist(): void
  {
    $this->created_at = new DateTime();
    $this->updated_at = new DateTime();
  }

  /**
   * Hook on pre-update operations.
   */
  public function preUpdate(): void
  {
    $this->updated_at = new DateTime();
  }

  //-----------------------------------------
  //-----------------------------------------
  //
  // SONATA BASE USER
  //
  //-----------------------------------------
  //-----------------------------------------

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $created_at;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $updated_at;

  /**
   * @var string
   */
  protected $gplusUid;

  /**
   * @var string
   */
  protected $gplusName;

  /**
   * @var string
   */
  protected $token;
  
  /**
   * Returns a string representation.
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getUsername() ?: '-';
  }

  public function setCreatedAt(?DateTime $created_at = null)
  {
    $this->created_at = $created_at;

    return $this;
  }

  public function getCreatedAt()
  {
    return $this->created_at;
  }

  public function setUpdatedAt(?DateTime $updated_at = null)
  {
    $this->updated_at = $updated_at;

    return $this;
  }

  public function getUpdatedAt()
  {
    return $this->updated_at;
  }

  public function setGroups($groups)
  {
    foreach ($groups as $group) {
      $this->addGroup($group);
    }

    return $this;
  }

  public function getGplusName()
  {
    return $this->gplusName;
  }

  public function setGplusUid($gplusUid)
  {
    $this->gplusUid = $gplusUid;

    return $this;
  }

  public function getGplusUid()
  {
    return $this->gplusUid;
  }

  public function setToken($token)
  {
    $this->token = $token;

    return $this;
  }

  public function getToken()
  {
    return $this->token;
  }

  public function getRealRoles()
  {
    return $this->roles;
  }

  public function setRealRoles(array $roles)
  {
    $this->setRoles($roles);

    return $this;
  }

  //-----------------------------------------
  //-----------------------------------------
  //
  // FOS USER
  //
  //-----------------------------------------
  //-----------------------------------------

  /**
   * @ORM\Column(type="string", length=180, nullable=false, unique=true)
   */
  protected $username;

  /**
   * @ORM\Column(type="string", length=180, nullable=false, name="username_canonical")
   */
  protected $usernameCanonical;

  /**
   * @ORM\Column(type="string", length=180, nullable=false, unique=true)
   */
  protected $email;

  /**
   * @ORM\Column(type="string", length=180, nullable=false, name="email_canonical")
   */
  protected $emailCanonical;

  /**
   * @ORM\Column(type="boolean", nullable=false)
   */
  protected $enabled;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   */
  protected $salt;

  /**
   * @ORM\Column(type="string", length=255, nullable=false)
   */
  protected $password;

  /**
   * Plain password. Used for model validation. Must not be persisted.
   *
   * @var string
   */
  protected $plain_password;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $last_login;

  /**
   * @ORM\Column(type="string", length=180, nullable=true)
   *
   * Random string sent to the user email address in order to verify it.
   * @var string|null
   */
  protected $confirmation_token;

  /**
   * @ORM\Column(type="datetime", nullable=true)
   */
  protected $password_requested_at;

  /**
   * @var GroupInterface[]|Collection
   */
  protected $groups;

  /**
   * @ORM\Column(type="array", nullable=false)
   */
  protected $roles;


  public function addRole($role)
  {
    $role = strtoupper($role);
    if ($role === static::ROLE_DEFAULT) {
      return $this;
    }

    if (!in_array($role, $this->roles, true)) {
      $this->roles[] = $role;
    }

    return $this;
  }

  public function serialize()
  {
    return serialize(array(
      $this->password,
      $this->salt,
      $this->usernameCanonical,
      $this->username,
      $this->enabled,
      $this->id,
      $this->email,
      $this->emailCanonical,
    ));
  }

  public function unserialize($serialized)
  {
    $data = unserialize($serialized);

    if (13 === count($data)) {
      // Unserializing a User object from 1.3.x
      unset($data[4], $data[5], $data[6], $data[9], $data[10]);
      $data = array_values($data);
    } elseif (11 === count($data)) {
      // Unserializing a User from a dev version somewhere between 2.0-alpha3 and 2.0-beta1
      unset($data[4], $data[7], $data[8]);
      $data = array_values($data);
    }

    list(
      $this->password,
      $this->salt,
      $this->usernameCanonical,
      $this->username,
      $this->enabled,
      $this->id,
      $this->email,
      $this->emailCanonical
      ) = $data;
  }

  public function eraseCredentials()
  {
    $this->plain_password = null;
  }

  public function getUsername()
  {
    return $this->username;
  }

  public function getUsernameCanonical()
  {
    return $this->usernameCanonical;
  }

  public function getSalt()
  {
    return $this->salt;
  }

  public function getEmail()
  {
    return $this->email;
  }

  public function getEmailCanonical()
  {
    return $this->emailCanonical;
  }

  public function getPassword()
  {
    return $this->password;
  }

  public function getPlainPassword()
  {
    return $this->plain_password;
  }

  /**
   * Gets the last login time.
   *
   * @return DateTime|null
   */
  public function getLastLogin()
  {
    return $this->last_login;
  }

  public function getConfirmationToken()
  {
    return $this->confirmation_token;
  }

  public function getRoles()
  {
    $roles = $this->roles;

    foreach ($this->getGroups() as $group) {
      $roles = array_merge($roles, $group->getRoles());
    }

    // we need to make sure to have at least one role
    $roles[] = static::ROLE_DEFAULT;

    return array_unique($roles);
  }

  public function hasRole($role)
  {
    return in_array(strtoupper($role), $this->getRoles(), true);
  }

  public function isAccountNonExpired()
  {
    return true;
  }

  public function isAccountNonLocked()
  {
    return true;
  }

  public function isCredentialsNonExpired()
  {
    return true;
  }

  public function isEnabled()
  {
    return $this->enabled;
  }

  public function isSuperAdmin()
  {
    return $this->hasRole(static::ROLE_SUPER_ADMIN);
  }

  public function removeRole($role)
  {
    if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
      unset($this->roles[$key]);
      $this->roles = array_values($this->roles);
    }

    return $this;
  }

  public function setUsername($username)
  {
    $this->username = $username;

    return $this;
  }

  public function setUsernameCanonical($usernameCanonical)
  {
    $this->usernameCanonical = $usernameCanonical;

    return $this;
  }

  public function setSalt($salt)
  {
    $this->salt = $salt;

    return $this;
  }

  public function setEmail($email)
  {
    $this->email = $email;

    return $this;
  }

  public function setEmailCanonical($emailCanonical)
  {
    $this->emailCanonical = $emailCanonical;

    return $this;
  }

  public function setEnabled($boolean)
  {
    $this->enabled = (bool) $boolean;

    return $this;
  }

  public function setPassword($password)
  {
    $this->password = $password;

    return $this;
  }

  public function setSuperAdmin($boolean)
  {
    if (true === $boolean) {
      $this->addRole(static::ROLE_SUPER_ADMIN);
    } else {
      $this->removeRole(static::ROLE_SUPER_ADMIN);
    }

    return $this;
  }

  public function setPlainPassword($password)
  {
    $this->plain_password = $password;

    return $this;
  }

  public function setLastLogin(DateTime $time = null)
  {
    $this->last_login = $time;

    return $this;
  }

  public function setConfirmationToken($confirmation_token)
  {
    $this->confirmation_token = $confirmation_token;

    return $this;
  }

  public function setPasswordRequestedat(DateTime $date = null)
  {
    $this->password_requested_at = $date;

    return $this;
  }

  /**
   * Gets the timestamp that the user requested a password reset.
   *
   * @return null|DateTime
   */
  public function getPasswordRequestedat()
  {
    return $this->password_requested_at;
  }

  public function isPasswordRequestNonExpired($ttl)
  {
    return $this->getPasswordRequestedat() instanceof DateTime &&
      $this->getPasswordRequestedat()->getTimestamp() + $ttl > time();
  }

  public function setRoles(array $roles)
  {
    $this->roles = array();

    foreach ($roles as $role) {
      $this->addRole($role);
    }

    return $this;
  }

  public function getGroups()
  {
    return $this->groups ?: $this->groups = new ArrayCollection();
  }

  public function getGroupNames()
  {
    $names = array();
    foreach ($this->getGroups() as $group) {
      $names[] = $group->getName();
    }

    return $names;
  }

  public function hasGroup($name)
  {
    return in_array($name, $this->getGroupNames());
  }

  public function addGroup(GroupInterface $group)
  {
    if (!$this->getGroups()->contains($group)) {
      $this->getGroups()->add($group);
    }

    return $this;
  }

  public function removeGroup(GroupInterface $group)
  {
    if ($this->getGroups()->contains($group)) {
      $this->getGroups()->removeElement($group);
    }

    return $this;
  }

  //-----------------------------------------
  //-----------------------------------------
  //
  // Custom USER
  //
  //-----------------------------------------
  //-----------------------------------------


//  const ROLE_DEFAULT = 'ROLE_USER';
//
//  const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

  
  public static string $SCRATCH_PREFIX = 'Scratch:';
  /**
   * @ORM\Id
   * @ORM\Column(name="id", type="guid")
   * @ORM\GeneratedValue(strategy="CUSTOM")
   * @ORM\CustomIdGenerator(class="App\Utils\MyUuidGenerator")
   *
   * @var string
   */
  protected $id;

  /**
   * @deprecated API v1
   *
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $upload_token = null;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected ?string $avatar = null;

  /**
   * @ORM\Column(type="string", length=75, nullable=false, options={"default": ""})
   */
  protected string $country = '';

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected ?string $additional_email = null;

  /**
   * Programs owned by this user.
   * When this user is deleted, all the programs owned by him should be deleted too.
   *
   * @ORM\OneToMany(
   *     targetEntity="Program",
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $programs;

  /**
   * Notifications which are available for this user (shown upon login).
   * When this user is deleted, all notifications for him should also be deleted.
   *
   * @ORM\OneToMany(
   *     targetEntity="CatroNotification",
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $notifications;

  /**
   * Comments written by this user.
   * When this user is deleted, all the comments he wrote should be deleted too.
   *
   * @ORM\OneToMany(
   *     targetEntity="UserComment",
   *     mappedBy="user",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $comments;

  /**
   * FollowNotifications mentioning this user as a follower.
   * When this user will be deleted, all FollowNotifications mentioning
   * him as a follower, should also be deleted.
   *
   * @ORM\OneToMany(
   *     targetEntity="App\Entity\FollowNotification",
   *     mappedBy="follower",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $follow_notification_mentions;

  /**
   * LikeNotifications mentioning this user as giving a like to another user.
   * When this user will be deleted, all LikeNotifications mentioning
   * him as a user giving a like to another user, should also be deleted.
   *
   * @ORM\OneToMany(
   *     targetEntity="App\Entity\LikeNotification",
   *     mappedBy="like_from",
   *     fetch="EXTRA_LAZY",
   *     cascade={"remove"}
   * )
   */
  protected Collection $like_notification_mentions;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\User", mappedBy="following")
   */
  protected Collection $followers;

  /**
   * @ORM\ManyToMany(targetEntity="\App\Entity\User", inversedBy="followers")
   */
  protected Collection $following;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\ProgramLike",
   *     mappedBy="user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserLikeSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserLikeSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $reverse_relations_of_similar_users_based_on_likes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserRemixSimilarityRelation",
   *     mappedBy="first_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $relations_of_similar_users_based_on_remixes;

  /**
   * @ORM\OneToMany(
   *     targetEntity="\App\Entity\UserRemixSimilarityRelation",
   *     mappedBy="second_user",
   *     cascade={"persist", "remove"},
   *     orphanRemoval=true
   * )
   */
  protected Collection $reverse_relations_of_similar_users_based_on_remixes;

  /**
   * @deprecated
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $gplus_access_token = null;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $google_id = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $facebook_id = null;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $google_access_token = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $facebook_access_token = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $apple_id = null;
  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $apple_access_token = null;
  /**
   * @deprecated
   * @ORM\Column(type="string", length=5000, nullable=true)
   */
  protected ?string $gplus_id_token = null;

  /**
   * @deprecated
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  protected ?string $gplus_refresh_token = null;

  /**
   * @ORM\Column(type="integer", nullable=true, unique=true)
   */
  protected ?int $scratch_user_id = null;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $oauth_password_created = false;

  /**
   * @ORM\Column(type="boolean", options={"default": false})
   */
  protected bool $oauth_user = false;

  /**
   * @ORM\OneToMany(targetEntity="App\Entity\ProgramInappropriateReport", mappedBy="reportingUser", fetch="EXTRA_LAZY")
   */
  protected Collection $program_inappropriate_reports;

  public function __construct()
  {
    $this->enabled = false;
    $this->roles = array();
    $this->programs = new ArrayCollection();
    $this->notifications = new ArrayCollection();
    $this->comments = new ArrayCollection();
    $this->follow_notification_mentions = new ArrayCollection();
    $this->like_notification_mentions = new ArrayCollection();
    $this->followers = new ArrayCollection();
    $this->following = new ArrayCollection();
    $this->likes = new ArrayCollection();
    $this->relations_of_similar_users_based_on_likes = new ArrayCollection();
    $this->reverse_relations_of_similar_users_based_on_likes = new ArrayCollection();
    $this->relations_of_similar_users_based_on_remixes = new ArrayCollection();
    $this->reverse_relations_of_similar_users_based_on_remixes = new ArrayCollection();
    $this->program_inappropriate_reports = new ArrayCollection();
  }

  public function getAppleId(): ?string
  {
    return $this->apple_id;
  }

  public function setGplusAccessToken(?string $gplus_access_token): void
  {
    $this->gplus_access_token = $gplus_access_token;
  }

  public function getGplusAccessToken(): ?string
  {
    return $this->gplus_access_token;
  }

  public function setGplusIdToken(?string $gplus_id_token): void
  {
    $this->gplus_id_token = $gplus_id_token;
  }

  public function getGplusIdToken(): ?string
  {
    return $this->gplus_id_token;
  }

  public function setGplusRefreshToken(?string $gplus_refresh_token): void
  {
    $this->gplus_refresh_token = $gplus_refresh_token;
  }

  public function getGplusRefreshToken(): ?string
  {
    return $this->gplus_refresh_token;
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function addProgram(Program $program): User
  {
    $this->programs[] = $program;

    return $this;
  }

  public function removeProgram(Program $program): void
  {
    $this->programs->removeElement($program);
  }

  public function getPrograms(): Collection
  {
    return $this->programs;
  }

  public function getUploadToken(): ?string
  {
    return $this->upload_token;
  }

  public function setUploadToken(?string $upload_token): void
  {
    $this->upload_token = $upload_token;
  }

  public function getCountry(): string
  {
    return $this->country;
  }

  public function setCountry(string $country): User
  {
    $this->country = $country;

    return $this;
  }

  public function setId(string $id): void
  {
    $this->id = $id;
  }

  public function setAdditionalEmail(?string $additional_email): void
  {
    $this->additional_email = $additional_email;
  }

  public function getAdditionalEmail(): ?string
  {
    return $this->additional_email;
  }

  public function getAvatar(): ?string
  {
    return $this->avatar;
  }

  public function setAvatar(?string $avatar): User
  {
    $this->avatar = $avatar;

    return $this;
  }

  public function getLikes(): Collection
  {
    return $this->likes;
  }

  public function setLikes(Collection $likes): void
  {
    $this->likes = $likes;
  }

  public function getFollowers(): Collection
  {
    return $this->followers;
  }

  public function addFollower(User $follower): void
  {
    $this->followers->add($follower);
  }

  public function removeFollower(User $follower): void
  {
    $this->followers->removeElement($follower);
  }

  public function hasFollower(User $user): bool
  {
    return $this->followers->contains($user);
  }

  public function getFollowing(): Collection
  {
    return $this->following;
  }

  public function addFollowing(User $follower): void
  {
    $this->following->add($follower);
  }

  public function removeFollowing(User $follower): void
  {
    $this->following->removeElement($follower);
  }

  public function isFollowing(User $user): bool
  {
    return $this->following->contains($user);
  }

  /**
   * Returns the FollowNotifications mentioning this user as a follower.
   */
  public function getFollowNotificationMentions(): Collection
  {
    return $this->follow_notification_mentions;
  }

  /**
   * Sets the FollowNotifications mentioning this user as a follower.
   */
  public function setFollowNotificationMentions(Collection $follow_notification_mentions): void
  {
    $this->follow_notification_mentions = $follow_notification_mentions;
  }

  public function getProgramInappropriateReports(): Collection
  {
    return $this->program_inappropriate_reports;
  }

  public function getProgramInappropriateReportsCount(): int
  {
    $programs_collection = $this->getPrograms();
    $programs = $programs_collection->getValues();
    $count = 0;
    foreach ($programs as $program) {
      $count += $program->getReportsCount();
    }

    return $count;
  }

  public function getComments(): Collection
  {
    return $this->comments;
  }

  public function getReportedCommentsCount(): int
  {
    /** @var ArrayCollection $comments_collection */
    $comments_collection = $this->getComments();
    $criteria = Criteria::create()->andWhere(Criteria::expr()->eq('isReported', 1));

    return $comments_collection->matching($criteria)->count();
  }

  public function setGoogleId(?string $google_id): void
  {
    $this->google_id = $google_id;
  }

  public function getGoogleId(): ?string
  {
    return $this->google_id;
  }

  public function setGoogleAccessToken(?string $google_access_token): void
  {
    $this->google_access_token = $google_access_token;
  }

  public function getGoogleAccessToken(): ?string
  {
    return $this->google_access_token;
  }

  public function changeCreatedAt(DateTime $createdAt): void
  {
    $this->created_at = $createdAt;
  }

  public function getScratchUserId(): ?int
  {
    return $this->scratch_user_id;
  }

  public function isScratchUser(): bool
  {
    return null !== $this->scratch_user_id;
  }

  public function setScratchUsername(string $username): void
  {
    $this->setUsername(self::$SCRATCH_PREFIX.$username);
  }

  public function getScratchUsername(): string
  {
    return preg_replace('/^'.self::$SCRATCH_PREFIX.'/', '', $this->getUsername());
  }

  public function setScratchUserId(?int $scratch_user_id): void
  {
    $this->scratch_user_id = $scratch_user_id;
  }

  public function isOauthPasswordCreated(): bool
  {
    return $this->oauth_password_created;
  }

  public function setOauthPasswordCreated(bool $oauth_password_created): void
  {
    $this->oauth_password_created = $oauth_password_created;
  }

  public function isOauthUser(): bool
  {
    return $this->oauth_user;
  }

  public function setOauthUser(bool $oauth_user): void
  {
    $this->oauth_user = $oauth_user;
  }

  public function getFacebookId(): ?string
  {
    return $this->facebook_id;
  }

  public function setFacebookId(?string $facebook_id): void
  {
    $this->facebook_id = $facebook_id;
  }

  public function getFacebookAccessToken(): ?string
  {
    return $this->facebook_access_token;
  }

  public function setFacebookAccessToken(?string $facebook_access_token): void
  {
    $this->facebook_access_token = $facebook_access_token;
  }

  public function setAppleId(?string $apple_id): void
  {
    $this->apple_id = $apple_id;
  }

  public function getAppleAccessToken(): ?string
  {
    return $this->apple_access_token;
  }

  public function setAppleAccessToken(?string $apple_access_token): void
  {
    $this->apple_access_token = $apple_access_token;
  }
  
}
