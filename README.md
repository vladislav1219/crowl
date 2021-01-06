# Crowl
The open-source SEO crawler.  
A crawler made by SEOs for SEOs, free and compatible with any operating system.  


## Setup

Crowl uses Python3 (lowest version tested is 3.7.0).  
We recommend using a virtual environment manager such as [pyenv](https://github.com/pyenv/pyenv).  

Install requirements:  

	pip install -r requirements.txt  


## Configuration file

As of v0.2, Crowl now uses configuration files in order to store all configuration options for each project you're working on.  

Copy the `config.sample.ini` file to `yourproject.ini` and set your own settings.  
The required settings are `PROJECT_NAME` and `START_URL`. You can keep other default settings for now ;)  
Check out [the docs](https://www.crowl.tech/docs/) for more configuration options.  


## Usage

Once your configuration file is saved, simply launch your first crawl:  

    python crowl.py --conf yourproject.ini  

Read [the docs](https://www.crowl.tech/docs/) for more details on config options and usage.  


## Contributing

If you wish to contribute to this repository or to report an issue, please do this [on GitLab](https://gitlab.com/crowltech/crowl).  