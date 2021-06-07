players = [17,5,6,76]
players[2]
# print(players[2])
players[3] = 7
# print(players)
s=players + [4,11,29]
# print(s)
players.append(4)
# print(players)
print(players[:2]) #только 17,5
players[:2] = [0, 'true']
print(players) #[0, 'true', 6, 7, 4]
players[:2] = []
print(players) #[6, 7, 4]
players[:] = []
print(players) #пустой список