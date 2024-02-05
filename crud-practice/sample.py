from functools import reduce

test_list = ["I love dogs", "I love cats", "I love frogs", "I love you chick"];

print("The original list : " + str(test_list))

def combine_dicts(dict1, dict2):
    for key in dict2:
        if key in dict1:
            dict1[key].extend(dict2[key])
        else:
            dict1[key] = dict2[key]
            return dict1
        
        dict_list =[{s.split()[0]: s.split()[1:]} for s in test_list]

        result_dict = reduce(combine_dicts, dict_list)

        print("The key values list dictionary : " + str(result_dict))   