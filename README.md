# Minecraft Curse Modpack Downloader

A simple system, written in PHP, that will take a modpack `manifest.json` file and download each listed mod from the Curse Forge.

Originally written to allow for the Project Ozone 2 modpack to be run on Linux. Likely to work with any other listed on the Curse Forge.

## Usage

```bash
php app.php manifest mods-path
```

### Example Usage

The end goal here is to get Project Ozone 2 running on Ubuntu 16.04, from scratch.

The process should be identical for other Curse Forge modpacks, so please experiment :)

#### 1. Get the right version of Java installed.

For Ubuntu 16.04, we're given access to Java 8.

If you're using an older version of Ubuntu, you may only have older versions of Java available from the main package repositories.

As some mods are starting to use Java 8, let's install it via a PPA.

**--Not required on Ubuntu 16.04 or higher--**

Oracle Java:

OpenJDK:

```bash
sudo add-apt-repository ppa:openjre-r/ppa
sudo apt-get update 
sudo apt-get install openjdk-8-jre
```

```bash
sudo apt-add-repository ppa:webupd8team/java
sudo apt-get update
sudo apt-get install oracle-java8-installer
```

Now configure your system to use the correct version of Java:

```bash
sudo update-alternatives --config java
```

I'll let you chose the right version of Java.

#### 2. Install Minecraft in a way that allows for modpacks

[MultiMC](https://multimc.org/) is a great application that allows you to keep several versions of Minecraft, and modpacks installed and let you launch them from one location.

Install this somewhere on your system. Either using their `.deb`, or with the generic version. If you use the generic version, you will need to choose where to decompress the tarball. Within your home directory is fine (I use `~/bin/minecraft/MultiMC`).

#### 3. Download the ModPack from Curse Forge

I'm using Project Ozone 2. So we'll:

* Goto https://minecraft.curseforge.com/projects/project-ozone-2-reloaded
* Goto [Files](https://minecraft.curseforge.com/projects/project-ozone-2-reloaded/files)
* Find the latest release and goto that filename (As of writing: [Project Ozone 2: Reloaded 2.2.7 EFU4](https://minecraft.curseforge.com/projects/project-ozone-2-reloaded/files/2388313))
* Download the file to `~/Downloads` (where you download it to doesn't really matter, we only need the content for a short while)
* Decompress the `.zip`
* You should then have a directory such as `Project Ozone 2-2.2.7` with the content:
  * `manifest.json`
  * `modlist.html`
  * `overrides/`

The important bits are the `manifest.json` file and `overrides/` directory.

#### 3. Add a new instance to MultiMC

