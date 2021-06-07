var = 11
varSome = "some"

num1 = 22
num2 = 33

res = num1 + num2
num2 = 44
res = num1 + num2
num2 = 'some'

str1 = str(num1)#привели число к строке
res = str1 + num2
print(res)