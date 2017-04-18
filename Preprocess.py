import pandas as pd
import numpy as np
import json
import os
from numbers import Number

# Standardize column values with z-score standardization
def zscore(dataFrame,sourceField,destinationField):
    dataFrame[destinationField] = (dataFrame[sourceField]-dataFrame[sourceField].mean())/dataFrame[sourceField].std()

# Standardize column values with single factor standardization
def factor(dataFrame,sourceColumn,destinationColumn,factor):
      dataFrame[destinationColumn] = dataFrame[sourceColumn]/([factor]*len(dataFrame[sourceColumn]))

# Constants
URLprefix = os.path.dirname(os.path.abspath(__file__))
sourceURL = '/CityNx_June.csv'
##sourceURL = '/CityNx_Jan.csv' #for part 2 of the question
stdURL = '/Standardized_June.csv'
##sourceURL = '/Standardized_Jan.csv' #for part 2 of the question
resultURL = '/Costs_June.csv'
##sourceURL = '/Costs_Jan.csv' #for part 2 of the question
configURL = '/config.json'

csvfile = open(URLprefix+sourceURL,"rb")
outputFile = open(URLprefix+resultURL,'wb')
stdFile = open(URLprefix+stdURL,'wb')
configFile = open(URLprefix+configURL,'rb')

config = json.load(configFile)
configFile.close()

df = pd.read_csv(csvfile,header='infer',index_col=0)

print "The following is a sample of the original values"
print df.head(5)

resultFieldNames = df.columns.values.tolist()

for column in df.columns:
    option = config[column]['option']
    if option == 'zscore':
        zscore(df,column,column)
    elif option == 'None':
        pass
    elif option == 'Remove':
        resultFieldNames.remove(column)
    elif isinstance(option,Number):
        factor(df,column,column,option)

df = df[resultFieldNames]

print "The following is a sample of the standardized values"
print df.head(5)

# Write Standardized values to table
df.to_csv(stdFile,index=True)

print "Generating cost table. This may take a few minutes..."

# Construct cost table
costdf = pd.DataFrame(index=df.index,columns=df.index)
costdf = costdf.fillna(0.0)

for origin in df.index:
    for destination in df.index:
        cost = 0.0
        if origin == destination:
            costdf.set_value(origin,destination,cost)
            continue
        originInfo = df.loc[origin]
        destInfo = df.loc[destination]
        for index in originInfo.index:
            option = config[index]['diffoption']
            weight = config[index]['weight']
            if option == 'direct':
                cost += (abs(originInfo[index] - destInfo[index]))*weight
            elif option == 'utcdiff':
                cost += (min(abs(originInfo[index] - destInfo[index]),24-abs(originInfo[index] - destInfo[index])))*weight
                pass
        costdf.set_value(origin,destination,cost)

# Write cost table to file
costdf.to_csv(outputFile,index=True)

prin "Cost table generated."

csvfile.close()
outputFile.close()
stdFile.close()  
