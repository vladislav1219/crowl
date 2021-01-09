import argparse
import configparser
from scrapy.crawler import CrawlerProcess
from scrapy.settings import Settings
import scrapy
from utils import *
from spiders import Crowler
from pipelines import *
import time
import logging

# Vladislav --> import for pagerank
from pageRank import PageRank

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description="SEO crawler")
    parser.add_argument('--conf',help="Configuration file (required)",
        required=True, type=str)
    parser.add_argument('-r','--resume',help="Output name (resume crawl)",
        default=None, type=str)
    args = parser.parse_args()

    #######################
    # Parse the config file
    config = configparser.ConfigParser()
    config.optionxform=str #Config Keys are case sensitive, this preserves case
    config.read(args.conf)

    start_url = config.get('PROJECT','START_URL')
    # Check if start URL is valid
    if not validate_url(start_url):
        print("Start URL not valid, please enter a valid HTTP or HTTPS URL.")
        exit(1)
    project_name = config.get('PROJECT','PROJECT_NAME')

    # Crawler conf
    settings = get_settings()
    settings.set('USER_AGENT', config.get('CRAWLER','USER_AGENT', fallback='Crowl (+https://www.crowl.tech/)'))
    settings.set('ROBOTS_TXT_OBEY', config.getboolean('CRAWLER','ROBOTS_TXT_OBEY', fallback=True))
    settings.set(
        'DEFAULT_REQUEST_HEADERS', 
        {
            'Accept': config.get('CRAWLER','MIME_TYPES', fallback='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'),
            'Accept-Language': config.get('CRAWLER','ACCEPT_LANGUAGE', fallback='en')
        })
    settings.set('DOWNLOAD_DELAY', float(config.get('CRAWLER','DOWNLOAD_DELAY', fallback=0.5)))
    settings.set('CONCURRENT_REQUESTS', int(config.get('CRAWLER','CONCURRENT_REQUESTS', fallback=5)))

    # Extraction settings
    conf = {
        'url': start_url, 
        'links': config.getboolean('EXTRACTION','LINKS',fallback=False),
        'content': config.getboolean('EXTRACTION','CONTENT',fallback=False),
        'depth': int(config.get('EXTRACTION','DEPTH',fallback=5)),
        'exclusion_pattern': config.get('CRAWLER','EXCLUSION_PATTERN',fallback=None)
    }
    
    
    # TIM --> Add crawl to DB and get last crawl id

    db = db_connected_crowl_interface()
    cur = db.cursor()
    cur.execute("INSERT INTO crawls (name, domain) VALUES ('{}', '{}')".format(project_name, getDomain(start_url)))
    db.commit()
    lastId = str(cur.lastrowid)
    cur.execute("UPDATE lastId SET lastId=" + lastId)
    db.commit()
    cur.execute("UPDATE crawls SET nameId='{}' WHERE id={}".format(project_name + "_" + lastId, lastId))
    db.commit()
    db.close()

    # Output pipelines
    pipelines = dict()
    for pipeline, priority in config['OUTPUT'].items():
        pipelines[pipeline] = int(priority)

    settings.set('ITEM_PIPELINES', pipelines)
    
    # if MySQL Pipeline, we need to add settings
    if 'crowl.CrowlMySQLPipeline' in pipelines.keys():        
        settings.set('MYSQL_HOST',config['MYSQL']['MYSQL_HOST'])
        settings.set('MYSQL_PORT',config['MYSQL']['MYSQL_PORT'])
        settings.set('MYSQL_USER',config['MYSQL']['MYSQL_USER'])
        settings.set('MYSQL_PASSWORD',config['MYSQL']['MYSQL_PASSWORD'])

        # TIM --> Si mysql, db=1 dans la bdd
        db = db_connected_crowl_interface()
        cur = db.cursor()
        cur.execute("UPDATE crawls SET db=1 WHERE id={}".format(lastId))
        db.commit()
        db.close()

    #######################
    # New crawl
    if args.resume is None:
        # Add output name to the settings
        output_name = get_dbname(project_name, lastId)
        settings.set('OUTPUT_NAME',output_name)

        # If MySQL Pipeline we need to create the DB
        if 'crowl.CrowlMySQLPipeline' in pipelines.keys():
            create_database(
                output_name,
                config['MYSQL']['MYSQL_HOST'],
                config['MYSQL']['MYSQL_PORT'],
                config['MYSQL']['MYSQL_USER'],
                config['MYSQL']['MYSQL_PASSWORD'])
            create_urls_table(
                output_name,
                config['MYSQL']['MYSQL_HOST'],
                config['MYSQL']['MYSQL_PORT'],
                config['MYSQL']['MYSQL_USER'],
                config['MYSQL']['MYSQL_PASSWORD'])
            if config.getboolean('EXTRACTION','LINKS',fallback=False):
                create_links_table(
                    output_name,
                    config['MYSQL']['MYSQL_HOST'],
                    config['MYSQL']['MYSQL_PORT'],
                    config['MYSQL']['MYSQL_USER'],
                    config['MYSQL']['MYSQL_PASSWORD'])

    #######################
    # Resume crawl
    else:
        output_name = args.resume
        settings.set('OUTPUT_NAME',output_name)

    # Vladislav --> for logging data
    logging.basicConfig(filename="pagerank.log", level=logging.INFO)

    # Set JOBDIR to pause/resume crawls 
    settings.set('JOBDIR','crawls/{}'.format(output_name))

    try:
        process = CrawlerProcess(settings)
        process.crawl(Crowler, **conf)
        process.start()
    except:
        logging.info("error occured while scrapy")
        pass



    ####################################################
    # Vladislav --> to make pagerank
    
    time.sleep(5)

    logging.info("now getting pagerank just started!")
    pageRk = PageRank()

    starttimeForPR= time.time()

    # to get newly created db connection
    # mydb= pageRk.dbConnectByName('pagerank')
    mydb= pageRk.dbConnectByName(settings.get('OUTPUT_NAME'))

    # this is to make urls same style
    # logging.info("now doing update urls from urls!")
    # pageRk.updateUrlsFromUrls(mydb)

    # logging.info("now doing update urls from links!")
    # pageRk.updateUrlsFromLinks(mydb)

    # this is the varable to store urls in this type {url: url, outCounts: outCounts, incomes: incomes}
    allUFData= {}

    # get url counts from urls table.

    urlCount= pageRk.getCountFromTable('urls', mydb)

    # default PageRank of each url
    defaultPR= 1/urlCount

    print(defaultPR)

    # get all usefull data of each url in this type {url: url, outCounts: outCounts, incomes: incomes}.

    logging.info("now getting all usefull data of each url!")
    for i in range(urlCount+1):
        if i== 0:
            continue
        # get url from urls table.
        url= pageRk.getUrlFromUrls(i, mydb)

        # get canonical from urls table
        canonical= pageRk.getCanonicalFromUrls(i, mydb)

        # get outCounts of url
        outCounts= pageRk.getOutCountsFromUrls(i, mydb)

        # get incomes of this url from others
        incomes= list(pageRk.getIncomesFromLinks(url, mydb))
        allUFData[url]= {'url': url, 'outCounts': outCounts, 'incomes': incomes, 'canonical': canonical}

        print(str(i)+ " inserted in allUFData successfully!")

    logging.info("now got all usefull data of each url!")

    # create pageranks table if not exist

 
    existOrnot= pageRk.tableExist('pageranks', mydb)
    if existOrnot== "notExist":
        logging.info("now creating pageranks table!")
        pageRk.createPRtable(mydb)

    # get really useful type of allUFData
    for ufData in allUFData:
        pagerank= 0
        # reset allUFData with canonical logic
        if allUFData[ufData]['canonical']== None or allUFData[ufData]['canonical']== 'None' or allUFData[ufData]['canonical']== '' or allUFData[ufData]['url']== allUFData[ufData]['canonical']:
            allUFData[ufData]['pagerank']= None
        else:            # if url!= canonical then this url's pagerank= 0
            allUFData[ufData]['pagerank']= 0

            # if canonical is in this urls then extend it's incomes to canonical else ignore
            if allUFData[ufData]['canonical'] in allUFData:
                # tmpForTOextend= list(allUFData[allUFData[ufData]['canonical']]['incomes'])
                # tmpForWillextend= list(allUFData[ufData]['incomes'])
                # allUFData[allUFData[ufData]['canonical']]['incomes']= tuple(tmpForTOextend.extend(tmpForWillextend))
                allUFData[allUFData[ufData]['canonical']]['incomes'].extend(allUFData[ufData]['incomes'])

    print('now really useful data geted')
    logging.info('now really useful data geted')

    # this is iterate part for precise
    iterateCount= 100
    if iterateCount== 1:
        for ufData in allUFData:
            pagerank= 0

            # if pagerank== 0 then it means this is canonicalized to another so it's PR= 0
            if allUFData[ufData]['pagerank']== 0:
                urlOfPR= allUFData[ufData]['url']
                pageRk.insertIntoPageRank(urlOfPR, 0, mydb)
            else:
                for income in allUFData[ufData]['incomes']:
                    if type(income) is tuple:
                        if type(income[0]) is not str:
                            incomeUrl= income[0].decode("utf-8")
                        else:
                            incomeUrl= income[0]
                    else:
                        incomeUrl= income
                    if incomeUrl in allUFData:
                        pagerank+= defaultPR/allUFData[incomeUrl]['outCounts']
                urlOfPR= allUFData[ufData]['url']
                pageRk.insertIntoPageRank(urlOfPR, pagerank, mydb)
                print(ufData+ " inserted in DB!")
                # print(allUFData[ufData])
                # print(allUFData[ufData]['incomes'][0][0].decode("utf-8")+"\n")
        consumeTimeForPR= time.time()- starttimeForPR
        print("it took"+consumeTimeForPR+ " seconds to get PageRank")
        logging.info("it took"+consumeTimeForPR+ " seconds to get PageRank")
    elif iterateCount> 1:
        for ic in range(iterateCount):
            if ic== 0:
                for ufData in allUFData:
                    pagerank= 0

                    # if pagerank== 0 then it means this is canonicalized to another so it's PR= 0
                    if allUFData[ufData]['pagerank']== 0:
                        urlOfPR= allUFData[ufData]['url']
                        # pageRk.insertIntoPageRank(urlOfPR, 0, mydb)
                    else:
                        for income in allUFData[ufData]['incomes']:
                            if type(income) is tuple:
                                if type(income[0]) is not str:
                                    incomeUrl= income[0].decode("utf-8")
                                else:
                                    incomeUrl= income[0]
                            else:
                                incomeUrl= income
                            if incomeUrl in allUFData:
                                pagerank+= defaultPR/allUFData[incomeUrl]['outCounts']
                        allUFData[ufData]['pagerank']= pagerank
                        # pageRk.insertIntoPageRank(urlOfPR, pagerank, mydb)
                        # print(allUFData[ufData])
                        # print(allUFData[ufData]['incomes'][0][0].decode("utf-8")+"\n")
                print('now iterate '+str(ic+1))
                consumeTimeForPR= time.time()- starttimeForPR
                print("it took"+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
                logging.info('now iterate ' +str(ic+1))
                logging.info("it took"+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
            elif ic> 0 and ic< iterateCount-1:
                for ufData in allUFData:
                    pagerank= 0

                    # if pagerank== 0 then it means this is canonicalized to another so it's PR= 0
                    if allUFData[ufData]['pagerank']== 0:
                        urlOfPR= allUFData[ufData]['url']
                        # pageRk.insertIntoPageRank(urlOfPR, 0, mydb)
                    else:
                        for income in allUFData[ufData]['incomes']:
                            if type(income) is tuple:
                                if type(income[0]) is not str:
                                    incomeUrl= income[0].decode("utf-8")
                                else:
                                    incomeUrl= income[0]
                            else:
                                incomeUrl= income
                            if incomeUrl in allUFData:
                                pagerank+= allUFData[incomeUrl]['pagerank']/allUFData[incomeUrl]['outCounts']
                        allUFData[ufData]['pagerank']= pagerank
                        # pageRk.insertIntoPageRank(urlOfPR, pagerank, mydb)
                        # print(allUFData[ufData])
                        # print(allUFData[ufData]['incomes'][0][0].decode("utf-8")+"\n")
                print('now iterate '+str(ic+1))
                consumeTimeForPR= time.time()- starttimeForPR
                print("it took"+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
                logging.info('now iterate ' +str(ic+1))
                logging.info("it took"+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
            elif ic== iterateCount- 1:
                print('this is last iterate')
                logging.info('this is last iterate')
                for ufData in allUFData:
                    pagerank= 0

                    # if pagerank== 0 then it means this is canonicalized to another so it's PR= 0
                    if allUFData[ufData]['pagerank']== 0:
                        urlOfPR= allUFData[ufData]['url']
                        pageRk.insertIntoPageRank(urlOfPR, 0, mydb)
                    else:
                        for income in allUFData[ufData]['incomes']:
                            if type(income) is tuple:
                                if type(income[0]) is not str:
                                    incomeUrl= income[0].decode("utf-8")
                                else:
                                    incomeUrl= income[0]
                            else:
                                incomeUrl= income
                            if incomeUrl in allUFData:
                                pagerank+= allUFData[incomeUrl]['pagerank']/allUFData[incomeUrl]['outCounts']
                        urlOfPR= allUFData[ufData]['url']
                        pageRk.insertIntoPageRank(urlOfPR, pagerank, mydb)
                        print(ufData+ " inserted in DB!")
                        # print(allUFData[ufData])
                        # print(allUFData[ufData]['incomes'][0][0].decode("utf-8")+"\n")
                print('now iterate '+str(ic+1))
                consumeTimeForPR= time.time()- starttimeForPR
                print("it took "+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
                logging.info('now iterate ' +str(ic+1))
                logging.info("it took "+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
        print('over')
        logging.info('over')