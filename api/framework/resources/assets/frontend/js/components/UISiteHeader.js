import React, { useState, useMemo } from "react";

const stepNames = [
  "Video Decoding",
  "Network Check",
  "Database",
  "Authentication API",
  "Payment Service",
  "Integrity Check",
  "Cache System",
  "Load Balancer",
  "Email Service",
  "Notification System",
];

const MAX_BARS = 60;

function Step({ name, statuses }) {
  const filledCount = statuses.filter((s) => s === "ok").length;
  return (
    <div style={{ marginBottom: 24 }}>
      <div
        style={{
          marginBottom: 6,
          fontWeight: "600",
          color: "#222",
          display: "flex",
          justifyContent: "space-between",
          userSelect: "none",
        }}
      >
        <span>{name}</span>
        <span style={{ color: filledCount === MAX_BARS ? "#00a862" : "#999" }}>
          {filledCount === MAX_BARS ? "OK" : "..."}
        </span>
      </div>
      <div style={{ display: "flex", gap: 8 }}>
        {statuses.map((status, i) => (
          <div
            key={i}
            title="0 incidents"
            style={{
              width: 3,
              height: 25,
              backgroundColor: status === "ok" ? "#00a862" : "#d9534f",
              borderRadius: 1,
              cursor: "default",
            }}
          />
        ))}
      </div>
    </div>
  );
}

export default function UISiteHeader() {
  // Aleatoriza só uma vez o progresso de cada categoria
  const progress = useMemo(() => {
    const obj = {};
    stepNames.forEach((name) => {
      obj[name] = Array.from({ length: MAX_BARS }, () =>
        Math.random() < 0.8 ? "ok" : "fail"
      );
    });
    return obj;
  }, []);

  return (
    <div
      style={{
        minHeight: "100vh",
        backgroundColor: "#f9f9f9",
        color: "#222",
        fontFamily: "monospace",
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: 20,
      }}
    >
      <h1 style={{ marginBottom: 40, color: "#008a55" }}>
        API {window.config?.app_name || "Course Platform"}
      </h1>
      <div style={{ width: 650 }}>
        {stepNames.map((name) => (
          <Step key={name} name={name} statuses={progress[name]} />
        ))}
      </div>
    </div>
  );
}