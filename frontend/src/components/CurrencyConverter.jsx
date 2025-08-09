// src/components/CurrencyConverter.js
import React, { useState, useEffect } from "react";
import { ArrowUpDown, TrendingUp, TrendingDown } from "lucide-react";
import { forexService } from "../services/forexApi";

const CurrencyConverter = ({ currencies }) => {
  const [fromCurrency, setFromCurrency] = useState("USD");
  const [toCurrency, setToCurrency] = useState("MYR");
  const [amount, setAmount] = useState("1");
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  const handleConvert = async () => {
    if (!amount || amount <= 0) {
      setError("Please enter a valid amount");
      return;
    }

    setLoading(true);
    setError("");

    try {
      const response = await forexService.convertCurrency(
        fromCurrency,
        toCurrency,
        amount
      );
      if (response.success) {
        setResult(response.data);
      } else {
        setError(response.message || "Conversion failed");
      }
    } catch (err) {
      setError(err.message);
      // Fallback to mock data
      const mockRate = 1.2296;
      setResult({
        from: fromCurrency,
        to: toCurrency,
        amount: parseFloat(amount),
        converted_amount: (parseFloat(amount) * mockRate).toFixed(4),
        exchange_rate: mockRate,
      });
    } finally {
      setLoading(false);
    }
  };

  const swapCurrencies = () => {
    setFromCurrency(toCurrency);
    setToCurrency(fromCurrency);
    setResult(null);
  };

  const formatNumber = (num) => {
    return new Intl.NumberFormat("en-US", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 4,
    }).format(num);
  };

  return (
    <div className="bg-white rounded-xl shadow-lg p-6">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">
        Currency Converter
      </h2>

      <div className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* From Currency */}
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">
              From
            </label>
            <div className="flex space-x-2">
              <select
                value={fromCurrency}
                onChange={(e) => setFromCurrency(e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              >
                {Object.entries(currencies).map(([code, name]) => (
                  <option key={code} value={code}>
                    {code} - {name}
                  </option>
                ))}
              </select>
            </div>
            <input
              type="number"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              placeholder="Enter amount"
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg"
            />
          </div>

          {/* Swap Button */}
          <div className="flex items-center justify-center">
            <button
              onClick={swapCurrencies}
              className="p-3 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-colors"
            >
              <ArrowUpDown className="w-6 h-6" />
            </button>
          </div>

          {/* To Currency */}
          <div className="space-y-2">
            <label className="block text-sm font-medium text-gray-700">
              To
            </label>
            <div className="flex space-x-2">
              <select
                value={toCurrency}
                onChange={(e) => setToCurrency(e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              >
                {Object.entries(currencies).map(([code, name]) => (
                  <option key={code} value={code}>
                    {code} - {name}
                  </option>
                ))}
              </select>
            </div>
            <div className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-lg font-semibold text-gray-900">
              {result ? formatNumber(result.converted_amount) : "0.00"}
            </div>
          </div>
        </div>

        {error && (
          <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {error}
          </div>
        )}

        <button
          onClick={handleConvert}
          disabled={loading || !amount}
          className="w-full bg-indigo-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {loading ? "Converting..." : "Convert"}
        </button>

        {result && (
          <div className="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
            <div className="text-center">
              <div className="text-3xl font-bold text-indigo-900 mb-2">
                {formatNumber(result.amount)} {result.from} ={" "}
                {formatNumber(result.converted_amount)} {result.to}
              </div>
              <div className="text-indigo-600 mb-4">
                Exchange Rate: 1 {result.from} ={" "}
                {formatNumber(result.exchange_rate)} {result.to}
              </div>
              <div className="flex items-center justify-center space-x-2 text-sm text-indigo-500">
                <TrendingUp className="w-4 h-4" />
                <span>Live rates updated every 15 minutes</span>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default CurrencyConverter;
