import json
import time
import math

#this is algorithm to calculate PageRank from https://www.geeksforgeeks.org/page-rank-algorithm-implementation/

# You can easily understand from this.
# Suppose instead that page B had a link to pages C and A, page C had a link to page A, 
# and page D had links to all three pages. 
# Thus, upon the first iteration, page B would transfer half of its existing value, 
# or 0.125, to page A and the other half, 
# or 0.125, to page C. Page C would transfer all of its existing value, 0.25, to the only page it links to, A. 
# Since D had three outbound links, it would transfer one third of its existing value, or approximately 0.083, to A. 
# At the completion of this iteration, page A will have a PageRank of approximately 0.458.


# this is DB connect part
import pymysql



class PageRank():

    def dbConnectByName(self, dbname):
        mydb = pymysql.connect(host="localhost",
                     user="vladislav",
                     passwd="Pakatopopopopo*4",
                     db= dbname)
        return mydb
    # user="vladislav",
    # passwd="Pakatopopopopo*4",
    # this is to get row counts of table
    def getCountFromTable(self, table, mydb):
        mycursor = mydb.cursor()
        sql= "SELECT COUNT(*) FROM " + str(table)
        mycursor.execute(sql)
        urlCount= mycursor.fetchone()[0]
        mycursor.close()
        return urlCount

    # this is make urls to same type from urls table
    # ie. "http/asdfsdf/asdfasdf" and "http/asdfsdf/asdfasdf/" is not same in db.
    # so first make it same type.
    def updateUrlsFromUrls(self, mydb):
        print('now doing url update from urls! this could be take minutes if DB is huge. Please wait..\n')
        urlCount= self.getCountFromTable('urls', mydb)
        mycursor= mydb.cursor()
        
        for id in range(urlCount+1):
            if id== 0:
                continue
            sql= "SELECT url FROM urls WHERE id ="+ str(id)
            mycursor.execute(sql)
            urls= mycursor.fetchall()
            url= " "
            if len(urls)> 0:
                url1= urls[0]
                if type(url1) is tuple:
                    if type(url1[0]) is not str:
                        url= url1[0].decode("utf-8")
                    else:
                        url= url1[0]
                else:
                    url= url1
            if url[-1]=="/":
                url= url[:-1]
            sql= "UPDATE urls SET url= %s WHERE id= %s"
            val= (str(url), str(id))
            mycursor.execute(sql, val)
            mydb.commit()
        mycursor.close()

    # def makeSameType(self, url):
    #     if url[-1]== "/":
    #         url= url[:-1]
    #         return url
    #     return url
    

    # this is make urls to same type from links table
    # ie. "http/asdfsdf/asdfasdf" and "http/asdfsdf/asdfasdf/" is not same in db.
    # so first make it same type.

    def updateUrlsFromLinks(self, mydb):
        print('now doing url update from links! this could be take minutes if DB is huge. Please wait...\n')
        urlCount= self.getCountFromTable('links', mydb)
        mycursor = mydb.cursor()
        for id in range(urlCount+1): #39637
            if id== 0:
                continue
            sql= "SELECT * FROM links WHERE id ="+ str(id)
            mycursor.execute(sql)
            links= mycursor.fetchall()
            if len(links)> 0:
                link= links[0]
                source1= link[1]
                if type(source1) is not str:
                    source= source1.decode("utf-8")
                else:
                    source= source1
                target1= link[2]
                if type(target1) is not str:
                    target= target1.decode("utf-8")
                else:
                    target= target1
            if source[-1]=="/":
                source= source[:-1]
            if target[-1]=="/":
                target= target[:-1]
            sql= "UPDATE links SET source= %s, target= %s WHERE id= %s"
            val= (str(source), str(target), str(id))
            mycursor.execute(sql, val)
            mydb.commit()
        mycursor.close()

    # this is to get url from urls
    def getUrlFromUrls(self, id, mydb):
        mycursor = mydb.cursor()
        sql= "SELECT url FROM urls WHERE id=" + str(id)
        mycursor.execute(sql)
        url1= mycursor.fetchall()[0]
        if type(url1) is tuple:
            if type(url1[0]) is not str:
                url= url1[0].decode("utf-8")
            else:
                url= url1[0]
        else:
            url= url1
        mycursor.close()
        return url

    # this is to get outlinks of url from links
    def getOutCountsFromLinks(self, url, mydb):
        mycursor = mydb.cursor()
        sql= "SELECT COUNT(*) FROM links WHERE source= %s"
        val= (str(url),)
        mycursor.execute(sql, val)
        outCounts= mycursor.fetchone()[0]
        mycursor.close()
        if int(outCounts)== 0:
            outCounts= 1
        return outCounts

    # this is to get outlinks of url from urls
    def getOutCountsFromUrls(self, id, mydb):
        mycursor= mydb.cursor()
        sql= "SELECT outlinks FROM urls WHERE id= "+str(id)
        mycursor.execute(sql)
        outCounts= mycursor.fetchone()[0]
        mycursor.close()
        return int(outCounts)

    # this is to get incomes urls of url from links
    def getIncomesFromLinks(self, url, mydb):
        # time.sleep(0.1)
        mycursor = mydb.cursor()
        sql= "SELECT source FROM links WHERE target= %s"
        val= (str(url),)
        mycursor.execute(sql, val)

        incomes= mycursor.fetchall()
        # time.sleep(0.1)
        mycursor.close()
        return incomes

    # get canocialize in urls table
    def getCanonicalFromUrls(self, id, mydb):
        mycursor = mydb.cursor()
        sql= "SELECT canonical FROM urls WHERE id= %s"
        val= (str(id),)
        mycursor.execute(sql, val)
        canonical= mycursor.fetchone()
        if type(canonical) is tuple:
            canonical= canonical[0]
        mycursor.close()
        return canonical

    # def makeOjData(self, url, outCounts, incomes):
    #     return {url: url, outCounts: outCounts, incomes: incomes}


    # this is to insert pagerank into pageranks table
    def tableExist(self, tablename, mydb):
        mycursor = mydb.cursor()
        _SQL = """SHOW TABLES"""
        mycursor.execute(_SQL)
        results = mycursor.fetchall()

        print('All existing tables:', results) # Returned as a list of tuples

        results_list = [item[0] for item in results] # Conversion to list of str

        if tablename in results_list:
            return "exist"
        else:
            return "notExist"

    def createPRtable(self, mydb):
        mycursor = mydb.cursor()
        sql= "CREATE TABLE pageranks (id INT AUTO_INCREMENT PRIMARY KEY, url VARCHAR(500), pagerank VARCHAR(500))"
        mycursor.execute(sql)
        mycursor.close()

    def tenTypeForPR(self, allUFData):
        maxtmp= 0
        for ufData in allUFData:
            if allUFData[ufData]['pagerank']> maxtmp:
                maxtmp= allUFData[ufData]['pagerank']
        x= 10/maxtmp
        for ufData in allUFData:
            allUFData[ufData]['pagerank']= round(allUFData[ufData]['pagerank']* x, 5)
        return allUFData


    def insertIntoPageRank(self, url, pagerank, mydb):
        mycursor = mydb.cursor()
        sql = "INSERT INTO pageranks (url, pagerank) VALUES (%s, %s)"
        val = (url, pagerank)
        mycursor.execute(sql, val)
        mydb.commit()
        mycursor.close()
    

    