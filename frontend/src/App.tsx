import React, { useState, useEffect } from "react";
import { forexService } from "./services/forexApi";
import {
  TrendingUp,
  ArrowUpDown,
  Calendar,
  DollarSign,
  Globe,
} from "lucide-react";

const ForexExchangeApp: React.FC = () => {
  const [rates, setRates] = useState<Record<string, number>>({});
  const [currencies, setCurrencies] = useState<Record<string, string>>({});
  const [fromCurrency, setFromCurrency] = useState<string>("USD");
  const [toCurrency, setToCurrency] = useState<string>("MYR");
  const [amount, setAmount] = useState<string>("1");
  const [convertedAmount, setConvertedAmount] = useState<string>("0");
  const [loading, setLoading] = useState<boolean>(false);
  const [selectedDate, setSelectedDate] = useState<string>(
    new Date().toISOString().split("T")[0]
  );
  const [activeTab, setActiveTab] = useState<"converter" | "rates">(
    "converter"
  );

  // Tiada hardcode global; guna fallback hanya bila perlu dalam catch

  // Fetch currencies sekali dan rates ikut fromCurrency
  useEffect(() => {
    let isMounted = true;
    (async () => {
      try {
        const [currenciesResponse, ratesResponse] = await Promise.all([
          forexService.getCurrencies(),
          forexService.getCurrentRates(fromCurrency),
        ]);
        if (!isMounted) return;
        setCurrencies(currenciesResponse);
        setRates(ratesResponse.rates);
      } catch {
        if (!isMounted) return;
        const defaults: Record<string, string> = {
          USD: "US Dollar",
          EUR: "Euro",
          GBP: "British Pound",
          MYR: "Malaysian Ringgit",
          JPY: "Japanese Yen",
        };
        setCurrencies((prev) => (Object.keys(prev).length ? prev : defaults));
        // biar kosong jika gagal; UI masih jalan
      }
    })();
    return () => {
      isMounted = false;
    };
  }, [fromCurrency]);

  // Fetch rates ikut tarikh bila tab "rates" aktif
  useEffect(() => {
    let isMounted = true;
    if (activeTab !== "rates") return;
    (async () => {
      try {
        const response = await forexService.getRatesByDate(selectedDate);
        if (!isMounted) return;
        setRates(response.rates);
      } catch {
        // biarkan rates sedia ada jika gagal
      }
    })();
    return () => {
      isMounted = false;
    };
  }, [activeTab, selectedDate]);

  const handleConvert = async () => {
    if (!amount || parseFloat(amount) <= 0) return;
    setLoading(true);
    try {
      const response = await forexService.convertCurrency(
        fromCurrency,
        toCurrency,
        amount
      );
      setConvertedAmount(String(response.result.toFixed(4)));
    } catch {
      // fallback: cuba guna rates semasa jika ada
      const rate = rates[toCurrency];
      if (rate) {
        const result = (parseFloat(amount) * rate).toFixed(4);
        setConvertedAmount(result);
      }
    } finally {
      setLoading(false);
    }
  };

  const swapCurrencies = () => {
    setFromCurrency(toCurrency);
    setToCurrency(fromCurrency);
  };

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat("en-US", {
      style: "decimal",
      minimumFractionDigits: 4,
      maximumFractionDigits: 4,
    }).format(value);
  };

  const RatesGrid = () => (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
      {Object.entries(rates).map(([currency, rate]) => (
        <div
          key={currency}
          className="bg-gray-50 rounded-lg p-4 border hover:shadow-md transition-shadow"
        >
          <div className="text-sm font-medium text-gray-600 mb-1">
            {currency}
          </div>
          <div className="text-xl font-bold text-gray-900">
            {formatCurrency(rate)}
          </div>
          <div className="text-xs text-gray-500 mt-1">
            {currencies[currency] || ""}
          </div>
          <div className="flex items-center mt-2 text-xs">
            <TrendingUp className="w-3 h-3 text-green-500 mr-1" />
            <span className="text-green-600">+0.12%</span>
          </div>
        </div>
      ))}
    </div>
  );

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
      {/* Header */}
      <div className="bg-white rounded-xl shadow-lg p-6 mb-6 flex justify-between">
        <div className="flex items-center space-x-3">
          <div className="bg-indigo-600 p-2 rounded-lg">
            <Globe className="w-6 h-6 text-white" />
          </div>
          <div>
            <h1 className="text-2xl font-bold text-gray-900">
              Yet Another Forex
            </h1>
            <p className="text-gray-600">Real-time currency exchange rates</p>
          </div>
        </div>
        <div className="text-right">
          <div className="text-sm text-gray-500">Rates as of</div>
          <div className="text-lg font-semibold text-gray-900">31-03-2021</div>
        </div>
      </div>

      {/* Tabs */}
      <div className="bg-white rounded-xl shadow-lg mb-6">
        <div className="flex border-b">
          <button
            onClick={() => setActiveTab("converter")}
            className={`px-6 py-4 font-medium ${
              activeTab === "converter"
                ? "text-indigo-600 border-b-2 border-indigo-600"
                : "text-gray-500 hover:text-gray-700"
            }`}
          >
            <DollarSign className="w-4 h-4 inline mr-2" />
            Converter
          </button>
          <button
            onClick={() => setActiveTab("rates")}
            className={`px-6 py-4 font-medium ${
              activeTab === "rates"
                ? "text-indigo-600 border-b-2 border-indigo-600"
                : "text-gray-500 hover:text-gray-700"
            }`}
          >
            <TrendingUp className="w-4 h-4 inline mr-2" />
            Live Rates
          </button>
        </div>

        <div className="p-6">
          {activeTab === "converter" && (
            <div className="max-w-2xl mx-auto space-y-6">
              {/* Converter UI */}
              <div className="flex items-center space-x-4">
                <div className="flex-1">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    From
                  </label>
                  <div className="flex space-x-2">
                    <select
                      value={fromCurrency}
                      onChange={(e) => setFromCurrency(e.target.value)}
                      className="w-24 px-3 py-2 border rounded-lg"
                    >
                      {Object.keys(currencies).map((code) => (
                        <option key={code} value={code}>
                          {code}
                        </option>
                      ))}
                    </select>
                    <input
                      type="number"
                      value={amount}
                      onChange={(e) => setAmount(e.target.value)}
                      className="flex-1 px-4 py-2 border rounded-lg"
                    />
                  </div>
                </div>
                <button
                  onClick={swapCurrencies}
                  className="mt-6 p-2 text-gray-400 hover:text-indigo-600"
                >
                  <ArrowUpDown className="w-5 h-5" />
                </button>
                <div className="flex-1">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    To
                  </label>
                  <div className="flex space-x-2">
                    <select
                      value={toCurrency}
                      onChange={(e) => setToCurrency(e.target.value)}
                      className="w-24 px-3 py-2 border rounded-lg"
                    >
                      {Object.keys(currencies).map((code) => (
                        <option key={code} value={code}>
                          {code}
                        </option>
                      ))}
                    </select>
                    <div className="flex-1 px-4 py-2 bg-gray-50 border rounded-lg text-lg font-semibold">
                      {convertedAmount}
                    </div>
                  </div>
                </div>
              </div>

              <button
                onClick={handleConvert}
                disabled={loading}
                className="w-full bg-indigo-600 text-white py-3 px-6 rounded-lg"
              >
                {loading ? "Converting..." : "Convert"}
              </button>
            </div>
          )}

          {activeTab === "rates" && (
            <>
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl font-semibold">Exchange Rates</h2>
                <div className="flex items-center space-x-2">
                  <Calendar className="w-4 h-4 text-gray-500" />
                  <input
                    type="date"
                    value={selectedDate}
                    onChange={(e) => setSelectedDate(e.target.value)}
                    className="px-3 py-1 border rounded-md text-sm"
                  />
                </div>
              </div>
              <RatesGrid />
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default ForexExchangeApp;
