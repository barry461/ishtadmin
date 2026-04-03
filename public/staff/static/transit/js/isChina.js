function isUserInChinaByTimeZone() {
    const chinaTimeZones = [
        "Asia/Anshan",
        "Asia/Hotan",
        "Asia/Shenyang",
        "Asia/Baoding",
        "Asia/Huaibei",
        "Asia/Shenzhen",
        "Asia/Baotou",
        "Asia/Huainan",
        "Asia/Shigatse",
        "Asia/Bayi",
        "Asia/Hulunbuir",
        "Asia/Shijiazhuang",
        "Asia/Beijing",
        "Asia/Jiamusi",
        "Asia/Suzhou",
        "Asia/Benxi",
        "Asia/Jilin",
        "Asia/Taiyuan",
        "Asia/Chamdo",
        "Asia/Jinan",
        "Asia/Tangshan",
        "Asia/Changchun",
        "Asia/Jinzhou",
        "Asia/Tianjin",
        "Asia/Changde",
        "Asia/Kashgar",
        "Asia/Tianshui",
        "Asia/Changsha",
        "Asia/Korla",
        "Asia/Turpan",
        "Asia/Changzhou",
        "Asia/Kunming",
        "Asia/Urumqi",
        "Asia/Chengdu",
        "Asia/Lanchow",
        "Asia/Weihai",
        "Asia/Chongqing",
        "Asia/Langfang",
        "Asia/Wuhan",
        "Asia/Dalian",
        "Asia/Leshan",
        "Asia/uhu",
        "Asia/Dandong",
        "Asia/Lhasa",
        "Asia/Xainza",
        "Asia/Daqing",
        "Asia/Luoyang",
        "Asia/Xi'an",
        "Asia/Datong",
        "Asia/Mudanjiang",
        "Asia/Xiamen",
        "Asia/Dunhuang",
        "Asia/Nagqu Town",
        "Asia/Xianyang",
        "Asia/Foochow",
        "Asia/Nanchang",
        "Asia/Xilinhot",
        "Asia/Foshan",
        "Asia/Nanjing",
        "Asia/Xilinji",
        "Asia/Fushun",
        "Asia/Nanning",
        "Asia/Xining",
        "Asia/Gêrzê",
        "Asia/Nantong",
        "Asia/Xinmi",
        "Asia/Golmud",
        "Asia/Ningbo",
        "Asia/Xinyang",
        "Asia/Guangzhou",
        "Asia/Pudong",
        "Asia/Xuzhou",
        "Asia/Guilin",
        "Asia/Qiemo",
        "Asia/Yanji",
        "Asia/Guiyang",
        "Asia/Qingdao",
        "Asia/Yinchuan",
        "Asia/Haikou",
        "Asia/Qinhuangdao",
        "Asia/Yumen",
        "Asia/Hami",
        "Asia/Qiqihar",
        "Asia/Zhangjiajie",
        "Asia/Handan",
        "Asia/Saga",
        "Asia/Zhangjiakou",
        "Asia/Hangzhou",
        "Asia/Sanya",
        "Asia/Zhanjiang",
        "Asia/Harbin",
        "Asia/Shanghai",
        "Asia/Zhengzhou",
        "Asia/Hefei	",
        "Asia/Shantou",
        "Asia/Zhuhai",
        "Asia/Hohhot",
        "Asia/Ürümqi",
    ];
    const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    return chinaTimeZones.includes(timeZone);
}

async function isUserInChinaByIP() {
    try {
        const response = await fetch("http://ip-api.com/json/");
        const data = await response.json();
        return data.country === "China";
    } catch (error) {
        console.error("无法获取IP定位信息:", error);
        return false;
    }
}

async function isUserInChina() {
    const inChinaByTimeZone = isUserInChinaByTimeZone();
    const inChinaByIP = await isUserInChinaByIP();
    return inChinaByTimeZone || inChinaByIP;
}

// isUserInChina().then((inChina) => {
//     console.log("in china:", inChina);
// });