import pandas as pd
import numpy as np
import sys
import os

sourceURL = os.path.dirname(os.path.abspath(__file__))+'/Costs_June.csv'
origins = ['MOSCOW,Russia','Shanghai,China','BEIJING(PEKING),China','HONGKONG,China-HongKong','Melbourne,Australia','Zutphen,Netherlands','Monterey,CA_USA']
candidates = []
selectedCity = 'N/A'
minCost = sys.maxint

csvfile = open(sourceURL,"rb")

df = pd.read_csv(csvfile,header='infer',index_col=0)

candidates = df.index

for candidate in candidates:
    totalCost = 0.0
    for origin in origins:
        totalCost += df.loc[origin,candidate]
    if totalCost < minCost:
        minCost = totalCost
        selectedCity = candidate

print 'Selected city: ' + selectedCity
print 'Minimum cost: ' + str(minCost)
