<?php

namespace Dropcat\Services;

interface DropcatConfigurationInterface
{

    public function localEnvironmentAppName();

    public function localEnvironmentAppPath();

    public function localEnvironmentBuildId();

    public function localEnvironmentTmpPath();

    public function localEnvironmentSeparator();

    public function localEnvironmentDbImport();

    public function localEnvironmentTarName();

    public function localEnvironmentTarDir();

    public function localEnvironmentSshKeyPassword();

    public function localEnvironmentDrushFolder();

    public function localEnvironmentRun();

    public function localEnvironmentBackupServer();

    public function localEnvironmentBackupServerPort();

    public function localEnvironmentBackupServerUser();

    public function localEnvironmentBackupPath();

    public function localEnvironmentBackupDbName();

    public function remoteEnvironmentServerName();

    public function remoteEnvironmentSshUser();

    public function remoteEnvironmentTargetPath();

    public function remoteEnvironmentSshPort();

    public function remoteEnvironmentIdentifyFile();

    public function remoteEnvironmentWebRoot();

    public function remoteEnvironmentTempFolder();

    public function remoteEnvironmentAlias();

    public function remoteEnvironmentTargetDir();

    public function remoteEnvironmentRun();

    public function siteEnvironmentDrushAlias();

    public function siteEnvironmentDrushInstallOptions();

    public function siteEnvironmentBackupPath();

    public function siteEnvironmentConfigName();

    public function siteEnvironmentOriginalPath();

    public function siteEnvironmentSymLink();

    public function siteEnvironmentUrl();

    public function siteEnvironmentName();

    public function siteEnvironmentProfile();

    public function siteEnvironmentBackupDbPath();

    public function deployIgnoreFiles();

    public function deployVarnishIP();

    public function deployVarnishPort();

    public function mysqlEnvironmentHost();

    public function mysqlEnvironmentDataBase();

    public function mysqlEnvironmentUser();

    public function mysqlEnvironmentPort();

    public function mysqlEnvironmentPassword();

    public function deployJenkinsServer();

    public function deployJenkinsJob();

    public function siteEnvironmentAdminPass();

    public function siteEnvironmentAdminUser();

    public function remoteEnvironmentLocalSshPort();

    public function nodeNvmDirectory();

    public function nodeNvmRcFile();

    public function gulpDirectory();

    public function gulpOptions();

    public function nodeEnvironment();

    public function localEnvironmentRsyncFrom();

    public function localEnvironmentRsyncTo();

    public function remoteEnvironmentRsyncFrom();

    public function remoteEnvironmentRsyncTo();

    public function remoteEnvironmentLocalServerName();

    public function remoteEnvironmentLocalSshUser();

    public function vhostFileName();

    public function vhostTarget();

    public function vhostBashCommand();

    public function vhostPort();

    public function vhostDocumentRoot();

    public function vhostServerName();

    public function getServerAliases();

    public function getServerExtras();

    public function trackerDir();

    public function trackerDbDump();

    public function trackerDbUser();

    public function trackerDbPass();

    public function trackerDbName();

    public function trackerDbHost();

    public function trackerId();

    public function trackerSitePath();

    public function trackerFile();

    public function rollbackId();

    public function createSite();

    public function syncFolder();

    public function configSplitFolder();

    public function drupalInstallProfile();

    public function siteSettings();

    public function siteConfig();
}
