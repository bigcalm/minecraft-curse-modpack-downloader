# Minecraft Curse Modpack Downloader

A simple system, written in PHP, that will take a modpack `manifest.json` file and download each listed mod from the Curse Forge, then copy over the override files specific to the ModPack.

Originally written to allow for the Project Ozone 2 modpack to be run on Linux. Likely to work with any other listed on the Curse Forge.

The app will download each mod that it can. For any mods that fail to download, the app will attempt to find the mod's file that is compatible with the ModPack's stated version of Minecraft.

## Usage

```bash
php app.php path/to/decompressed-mod-pack path/to/minecraft-instance
```

### Example Usage

The end goal here is to get Project Ozone 3 running on Ubuntu 16.04, from scratch.

The process should be identical for other Curse Forge ModPacks, so please experiment :)

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

Install this somewhere on your system. Either using their `.deb`, or with the generic version. If you use the generic version, you will need to compile the source code and note where the package is installed. I use `~/MultiMC/install`.

#### 3. Download the ModPack from Curse Forge

I'm using Project Ozone 3. So we'll:

* Goto https://minecraft.curseforge.com/projects/project-ozone-3-a-new-way-forward
* Goto [Files](https://minecraft.curseforge.com/projects/project-ozone-3-a-new-way-forward/files)
* Find the latest release and goto that filename (As of writing: [PO3-3.0.40](https://minecraft.curseforge.com/projects/project-ozone-3-a-new-way-forward/files/2700945))
* Download the file to `~/Downloads` (where you download it to doesn't really matter, we only need the content for a short while)
* Decompress the `.zip`: `unzip ~/Downloads/PO3-3.0.40.zip -d ~/Downloads/'Project Ozone 3 - 3.0.40'`
* You should then have a directory like this:

```
$ tree ~/Downloads/'Project Ozone 3 - 3.0.40' -L 1
/home/iain/Downloads/Project Ozone 3 - 3.0.40
├── manifest.json
├── modlist.html
└── overrides

1 directory, 2 files
```

The important bits are the `manifest.json` file and `overrides` directory.

Take a look at the top of the `mainfest.json` file.

I used:

```bash
$ head ~/Downloads/'Project Ozone 3 - 3.0.40'/manifest.json
```

Which produced:

```json
{
  "minecraft": {
    "version": "1.12.2",
    "modLoaders": [
      {
        "id": "forge-14.23.5.2836",
        "primary": true
      }
    ]
  },
```

We need to know the version of Minecraft the ModPack is for. And the version of Forge is will use.

Looking at the above output, we have:

* Minecraft Version: 1.12.2
* Forge Version: 14.23.5.2836

Keep this information for the next step.

#### 3. Add a new instance to MultiMC

I'm not going to pretend I know everything about MultiMC, and I won't try to document it all.

But here are the minimum steps needed to move forwards.

* Add a new instance (button top left or right click in the blank area).
* Give the instance a meaningful name. I shall use `Project Ozone 3 - 3.0.40`.
* For `Vanilla Minecraft (select version)` chose the version that was obtained from the `manifest.json` file. `1.12.2` in this instance.
* Hit `OK` to save your selections.
* Right click on the new instance icon and choose `Edit instance`
* In the right hand coloumn of the new window are a load of buttons, hit `Install Forge`
    * Choose the version of Forge that matches the info in the `manifest.json` file. `14.23.5.2836` in this instance.
* In the left hand column of the window, select `Settings`
    * Enable the `Memory` section and set the `Maximum memory allocation` to something greater than 6GB. I have mine set to 8GB: `8192 MB`.
* Click `Close`

#### 4. Install the downloader requirements

If you haven't already, clone the this git repo and enter the directory:
```bash
git clone https://github.com/bigcalm/minecraft-curse-modpack-downloader.git
cd minecraft-curse-modpack-downloader
```

I will assume that you have PHP installed.

Run:

```bash
./composer.phar install
```

That was easy.

#### 5. Download the ModPack mods!

Finally we are ready to download some sweet mods.

To do so, we needed 2 things:

1. The path to the modpack you decompressed.
2. The location for of the minecraft instance.

As I have chosen to name the instance as `Project Ozone 3 - 3.0.40`, the mods directory will be `~/MultiMC/install/instances/'Project Ozone 3 - 3.0.40'/.minecraft`.

Run the PHP app, providing the full path to the decompressed modpack and the instance's `.minecraft` directory.

```bash
php app.php ~/Downloads/'Project Ozone 3 - 3.0.40' ~/MultiMC/install/instances/'Project Ozone 3 - 3.0.40'/.minecraft
```

This will:
1. parse the `manifest.json` file
2. download each mod file into the `mods` directory
3. copy the `overrides` directory over the top of the `.minecraft` directory

If any of the listed mod files are not available for download, then the app will attempt to download the latest version that is compatible with the modpack's minecraft version.

This might not always desired, but the app will display information as to what it is doing. Allowing you to remove unwanted mods.

#### 6. Play

Back in MultiMC start the instance we created before.
