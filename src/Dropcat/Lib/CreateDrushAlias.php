<?php
namespace Dropcat\Lib;

class CreateDrushAlias
{
    private $sitename;
    private $server;
    private $user;
    private $webroot;
    private $alias;
    private $url;
    private $sshport;

    public function setName($sitename)
    {
        $this->setName = $sitename;
    }

    public function setServer($server)
    {
        $this->setServer = $server;
    }

    public function setUser($user)
    {
        $this->setUser = $user;
    }

    public function setWebRoot($webroot)
    {
        $this->setWebRoot = $webroot;
    }

    public function setSitePath($alias)
    {
        $this->setSitePath = $alias;
    }

    public function setUrl($url)
    {
        $this->setUrl = $url;
    }

    public function setSSHPort($sshport)
    {
        $this->setSSHPort = $sshport;
    }


    public function getValue()
    {
        $aliasOut = '<?php
  $aliases["' . $this->setName . '"] = array (
    "remote-host" => "' . $this->setServer . '",
    "remote-user" => "' . $this->setUser . '",
    "root" => "' . $this->setWebRoot . '/' . $this->setSitePath . '/web",
    "uri"  => "' . $this->setUrl . '",
    "ssh-options" => "-q -p ' . $this->setSSHPort . '",
);';
        return ($aliasOut);
    }


  /**
   * Return the user's home directory.
   * Copied from drush_server_home().
   */
    public function drushServerHome()
    {
      // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
      // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = getenv('HOME');
        if (!empty($home)) {
          // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
          // home on windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
          // If HOMEPATH is a root directory the path can end with a slash. Make sure
          // that doesn't happen.
            $home = rtrim($home, '\\/');
        }
        return empty($home) ? null : $home;
    }
}

// Usage example
//
//$drushAlias = new CreateDrushAlias();
//$drushAlias->setName($siteName);
//$drushAlias->setServer($server);
//$drushAlias->setUser($user);
//$drushAlias->setWebRoot($webroot);
//$drushAlias->setSitePath($alias);
//$drushAlias->setUrl($url);
//$drushAlias->setSSHPort($sshport);
//
//$home = new CreateDrushAlias();
//$home_dir = $home->drushServerHome();
//
//$drush_alias_name = 'myalias';
//
//$drush_file = new Filesystem();
//
//try {
//  $drush_file->dumpFile($home_dir.'/.drush/'.$drush_alias_name.
//    '.aliases.drushrc.php', $drushAlias->getValue());
//} catch (IOExceptionInterface $e) {
//  echo 'An error occurred while creating your file at '.$e->getPath();
//}
