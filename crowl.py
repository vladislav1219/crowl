
import time
import logging

# Vladislav --> import for pagerank
from pageRank import PageRank

if __name__ == '__main__':
    logging.basicConfig(filename="pageranksInfo.log", level=logging.INFO)

    logging.info("now getting pagerank just started!")
    pageRk = PageRank()

    starttimeForPR= time.time()

    # to get newly created db connection
    mydb= pageRk.dbConnectByName('pagerank')
    # mydb= pageRk.dbConnectByName(settings.get('OUTPUT_NAME'))

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
    iterateCount= 10
    if iterateCount== 1:
        for ufData in allUFData:
            pagerank= 0

            # if pagerank== 0 then it means this is canonicalized to another so it's PR= 0
            if allUFData[ufData]['pagerank']== 0:
                urlOfPR= allUFData[ufData]['url']
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
                allUFData[ufData]['pagerank']= pagerank
                print(ufData+ " inserted in DB!")
                # print(allUFData[ufData])
                # print(allUFData[ufData]['incomes'][0][0].decode("utf-8")+"\n")
        allUFData= pageRk.tenTypeForPR(allUFData)
        for ufData in allUFData:
            urlOfPR= allUFData[ufData]['url']
            pageRk.insertIntoPageRank(urlOfPR, allUFData[ufData]['pagerank'], mydb)
        consumeTimeForPR= time.time()- starttimeForPR
        print("it took"+str(consumeTimeForPR)+ " seconds to get PageRank")
        logging.info("it took"+str(consumeTimeForPR)+ " seconds to get PageRank")
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
                        # print(allUFData[ufData])
                        # print(allUFData[ufData]['incomes'][0][0].decode("utf-8")+"\n")
                allUFData= pageRk.tenTypeForPR(allUFData)

                print("now it's the time to insert into DB!")
                logging.info("now it's the time to insert into DB!")
                for ufData in allUFData:
                    urlOfPR= allUFData[ufData]['url']
                    pageRk.insertIntoPageRank(urlOfPR, allUFData[ufData]['pagerank'], mydb)
                print('now iterate '+str(ic+1))
                consumeTimeForPR= time.time()- starttimeForPR
                print("it took "+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
                logging.info('now iterate ' +str(ic+1))
                logging.info("it took "+str(consumeTimeForPR)+ " seconds for iterate"+ str(ic+1)+" to get PageRank")
        print('over')
        logging.info('over')